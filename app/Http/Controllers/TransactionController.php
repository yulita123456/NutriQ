<?php

namespace App\Http\Controllers;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Midtrans\Snap;
use Midtrans\Config;
use App\Exports\TransactionsExport;
use App\Exports\DashboardRekapExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\LogAktivitas;


class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $transactions = Transaction::with('details.product')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($transactions);
    }

    // Simpan transaksi baru
    public function store(Request $request)
    {
        \Log::info('[STORE TRANSACTION] REQUEST PAYLOAD', $request->all());

        $user = $request->user();

        $validated = $request->validate([
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:product,id',
            'products.*.qty' => 'required|integer|min:1',
        ]);
        \Log::info('[STORE TRANSACTION] DATA VALIDATED', $validated);

        DB::beginTransaction();

        try {
            $total = 0;
            $details = [];
            $item_details = [];

            foreach ($validated['products'] as $key => $item) {
                if (!isset($item['product_id']) || !isset($item['qty'])) {
                    \Log::error("[STORE TRANSACTION] Produk ke-{$key} tidak memiliki product_id atau qty", ['item' => $item]);
                    return response()->json([
                        'message' => "Produk ke-{$key} tidak memiliki product_id atau qty"
                    ], 400);
                }

                $product = Product::findOrFail($item['product_id']);
                if ($product->stock < $item['qty']) {
                    \Log::warning("[STORE TRANSACTION] Stok produk tidak cukup", [
                        'product' => $product->nama_produk,
                        'stok' => $product->stock,
                        'qty' => $item['qty']
                    ]);
                    return response()->json([
                        'message' => 'Stok produk ' . $product->nama_produk . ' tidak cukup'
                    ], 400);
                }
                $subtotal = $product->harga * $item['qty'];
                $total += $subtotal;

                $details[] = [
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'harga' => $product->harga,
                    'subtotal' => $subtotal,
                ];

                $product->stock -= $item['qty'];
                $product->save();

                $item_details[] = [
                    'id' => 'PROD' . $product->id,
                    'price' => $product->harga,
                    'quantity' => $item['qty'],
                    'name' => substr($product->nama_produk, 0, 50)
                ];
            }

            $orderId = 'TRX-' . strtoupper(uniqid());
            \Log::info('[STORE TRANSACTION] ORDER ID', ['order_id' => $orderId]);

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'total' => $total,
                'status' => 'pending',
                'order_id' => $orderId,
            ]);
            \Log::info('[STORE TRANSACTION] TRANSACTION CREATED', ['id' => $transaction->id]);

            foreach ($details as $dt) {
                $dt['transaction_id'] = $transaction->id;
                TransactionDetail::create($dt);
            }
            \Log::info('[STORE TRANSACTION] DETAILS SAVED', $details);

            // Midtrans Snap
            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = false;
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $total,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '08123456789',
                ],
                'item_details' => $item_details,
            ];
            \Log::info('[MIDTRANS CHECK] finish_redirect_url', [$params['finish_redirect_url'] ?? 'none']);

            \Log::info('[STORE TRANSACTION] MIDTRANS PARAMS', $params);

            try {
                $snapResponse = \Midtrans\Snap::createTransaction($params);
                \Log::info('[STORE TRANSACTION] MIDTRANS RESPONSE', (array)$snapResponse);
                $snapToken = is_array($snapResponse) ? $snapResponse['token'] : $snapResponse->token ?? null;
                $redirectUrl = is_array($snapResponse) ? $snapResponse['redirect_url'] : $snapResponse->redirect_url ?? null;
                if (!$snapToken || !$redirectUrl) {
                    \Log::error('[STORE TRANSACTION] Snap token/redirect url tidak ditemukan', ['response' => $snapResponse]);
                    DB::rollBack();
                    return response()->json(['message' => 'Snap token atau redirect url tidak ditemukan.'], 500);
                }
            } catch (\Exception $e) {
                \Log::error('[STORE TRANSACTION] MIDTRANS ERROR', ['error' => $e->getMessage()]);
                DB::rollBack();
                return response()->json(['message' => 'Transaksi gagal: ' . $e->getMessage()], 500);
            }

            // Simpan snap_token dan redirect_url ke database
            $transaction->snap_token = $snapToken;
            $transaction->redirect_url = $redirectUrl;
            $transaction->save();

            DB::commit();

            \Log::info('[STORE TRANSACTION] TRANSACTION FINALIZED', [
                'transaction_id' => $transaction->id,
                'order_id' => $orderId,
                'snap_token' => $snapToken,
                'redirect_url' => $redirectUrl,
            ]);

            // === LOG AKTIVITAS TRANSAKSI USER ===
            $produkList = array_map(function($dt) {
                return $dt['qty'] . 'x ' . Product::find($dt['product_id'])->nama_produk;
            }, $details);
            $produkListStr = implode(', ', $produkList);

            LogAktivitas::create([
                'user_id'   => $user->id,
                'role'      => $user->role ?? 'user',
                'aksi'      => 'checkout',
                'kategori'  => 'transaksi', // kategori transaksi
                'deskripsi' => 'Checkout order ' . $orderId . ' dengan total Rp' . number_format($total, 0, ',', '.') . ' (Produk: ' . $produkListStr . ')',
                'ip_address'=> $request->ip(),
            ]);

            return response()->json([
                'message' => 'Transaksi berhasil dibuat',
                'transaction_id' => $transaction->id,
                'order_id' => $orderId,
                'snap_token' => $snapToken,
                'redirect_url' => $redirectUrl,
            ], 201);

        } catch (\Exception $e) {
            \Log::error('[STORE TRANSACTION] FATAL ERROR', ['error' => $e->getMessage()]);
            DB::rollBack();
            return response()->json([
                'message' => 'Transaksi gagal: ' . $e->getMessage()
            ], 500);
        }
    }


public function show(Request $request, $id)
{
    $user = $request->user();
    $transaction = Transaction::with('details.product')
        ->where('user_id', $user->id)
        ->findOrFail($id);

    return response()->json($transaction);
}

public function midtransCallback(Request $request)
{
    \Log::info('MIDTRANS CALLBACK MASUK', $request->all());

    $serverKey = env('MIDTRANS_SERVER_KEY');
    $hashed = hash('sha512',
        $request->order_id . $request->status_code . $request->gross_amount . $serverKey
    );
    if ($hashed != $request->signature_key) {
        \Log::warning('MIDTRANS SIGNATURE NOT MATCH!');
        return response()->json(['message' => 'Invalid signature'], 403);
    }

    $transaction = Transaction::where('order_id', $request->order_id)->first();
    if (!$transaction) {
        \Log::warning('MIDTRANS ORDER ID NOT FOUND!');
        return response()->json(['message' => 'Order not found'], 404);
    }

    \Log::info('MIDTRANS TRANSACTION STATUS', [
        'status' => $request->transaction_status,
        'order_id' => $request->order_id,
    ]);

    if ($request->transaction_status == 'settlement') {
        $transaction->status = 'settlement';
    } elseif ($request->transaction_status == 'pending') {
        $transaction->status = 'pending';
    } elseif (in_array($request->transaction_status, ['expire', 'cancel', 'failure'])) {
        $transaction->status = 'failed';
    } else {
        $transaction->status = $request->transaction_status;
    }

    $transaction->save();

    \Log::info('MIDTRANS CALLBACK PROCESSED', [
        'order_id' => $request->order_id,
        'status'   => $transaction->status
    ]);
    return response()->json(['message' => 'Callback processed']);
}

public function showByOrderId(Request $request, $order_id)
{
    $user = $request->user();

    $transaction = Transaction::with('details.product')
        ->where('user_id', $user->id)
        ->where('order_id', $order_id)
        ->first();

    if (!$transaction) {
        return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
    }

    return response()->json($transaction);
}
public function adminLog(Request $request)
{
    $query = \App\Models\Transaction::with('user');

    // Filter bulan & tahun
    if ($request->filled('bulan')) {
        $query->whereMonth('created_at', $request->bulan);
    }
    if ($request->filled('tahun')) {
        $query->whereYear('created_at', $request->tahun);
    }
    // Filter status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    // Search Order ID atau Nama User
    if ($request->filled('q')) {
        $q = $request->q;
        $query->where(function ($sub) use ($q) {
            $sub->where('order_id', 'like', "%$q%")
                ->orWhereHas('user', function ($qu) use ($q) {
                    $qu->where('name', 'like', "%$q%");
                });
        });
    }

    $transactions = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
    return view('admin.transaksi.index', compact('transactions'));
}
public function adminShow($id)
{
    $transaction = \App\Models\Transaction::with(['user', 'details.product'])->findOrFail($id);

    return view('admin.transaksi.show', compact('transaction'));
}
public function exportExcel(Request $request)
{
    // Forward filter params
    $bulan = $request->bulan;
    $tahun = $request->tahun;
    $status = $request->status;

    return Excel::download(
        new TransactionsExport($bulan, $tahun, $status),
        "log_pembayaran_{$tahun}_{$bulan}_{$status}.xlsx"
    );
}
public function exportRekap(Request $request)
{
    $tahun = $request->input('tahun') ?: now()->year;
    $bulan = $request->input('bulan');
    $status = $request->input('status');

    $namaFile = $bulan
        ? "rekap_pembayaran_{$tahun}_{$bulan}.xlsx"
        : "rekap_pembayaran_{$tahun}.xlsx";

    return Excel::download(
        new DashboardRekapExport($tahun, $bulan, $status),
        $namaFile
    );
}
}
