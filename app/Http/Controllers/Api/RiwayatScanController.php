<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RiwayatScan;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Models\LogAktivitas;
class RiwayatScanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:product,id',
        ]);

        $user = Auth::user();
        $produk = Product::find($request->produk_id);

        // ======================================================
        // ==== TAMBAHKAN KONDISI PENGECEKAN DI SINI ====
        // ======================================================
        // Jika produk TIDAK punya data gizi, hentikan proses.
        if (!$produk->hasNutritionData()) {
            // Beri respon sukses tapi dengan pesan bahwa riwayat tidak dicatat.
            // Ini agar aplikasi Flutter tidak menampilkan error.
            return response()->json(['success' => true, 'message' => 'Riwayat tidak dicatat karena data gizi tidak tersedia.']);
        }
        // ======================================================

        // Penilaian sehat/tidak sehat dari model Product
        $isSehat = $produk->isSehat();

        $riwayat = RiwayatScan::create([
            'user_id' => $user->id,
            'produk_id' => $produk->id,
            'is_sehat' => $isSehat,
            'waktu_scan' => now(),
        ]);

        // === LOG AKTIVITAS SCAN MAKANAN ===
        LogAktivitas::create([
            'user_id'   => $user->id,
            'role'      => $user->role ?? 'user',
            'aksi'      => 'scan_makanan',
            'kategori'  => 'scan',
            'deskripsi' => 'Scan produk: ' . $produk->nama_produk . ' (Kode: ' . $produk->kode_produk . ') - ' . ($isSehat ? 'Sehat' : 'Tidak Sehat'),
            'ip_address'=> $request->ip(),
        ]);

        return response()->json(['success' => true, 'riwayat' => $riwayat]);
    }
    public function statistik()
    {
        $user = Auth::user();
        $totalSehat = RiwayatScan::where('user_id', $user->id)->where('is_sehat', true)->count();
        $totalTidakSehat = RiwayatScan::where('user_id', $user->id)->where('is_sehat', false)->count();
        $total = $totalSehat + $totalTidakSehat;

        // Ambil juga 5 scan terakhir (opsional)
        $lastScans = RiwayatScan::with('produk')
            ->where('user_id', $user->id)
            ->latest('waktu_scan')
            ->take(5)
            ->get();

        return response()->json([
            'sehat' => $totalSehat,
            'tidak_sehat' => $totalTidakSehat,
            'total' => $total,
            'last_scans' => $lastScans
        ]);
    }
}
