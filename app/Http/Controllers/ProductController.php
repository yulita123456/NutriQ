<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;
use thiagoalessio\TesseractOCR\TesseractOCR;




class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Filter pencarian nama produk
        if ($request->filled('search')) {
            $query->where('nama_produk', 'like', '%' . $request->search . '%');
        }

        // Filter kategori produk
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Filter harga minimum
        if ($request->filled('harga_min')) {
            $query->where('harga', '>=', $request->harga_min);
        }

        // Filter harga maksimum
        if ($request->filled('harga_max')) {
            $query->where('harga', '<=', $request->harga_max);
        }

        // Paginate hasil filter
        $products = $query->paginate(15);

        return view('admin.products.index', compact('products'));
    }
    public function create()
    {
        return view('admin.products.create');
    }

    public function storeByAdmin(Request $request)
    {
        $validated = $request->validate([
            'kode_produk'   => 'required|unique:product,kode_produk',
            'nama_produk'   => 'required',
            'kategori'      => 'required|in:minuman,snack dan cemilan,makanan instan',
            'stock'         => 'required|integer',
            'harga'         => 'required|integer|min:0',
            'kalori'        => 'nullable|integer',
            'lemak_total'   => 'nullable|numeric',
            'lemak_jenuh'   => 'nullable|numeric',
            'protein'       => 'nullable|numeric',
            'gula'          => 'nullable|numeric',
            'karbohidrat'   => 'nullable|numeric',
            'garam'         => 'nullable|numeric',
            'foto.*'        => 'image|mimes:jpg,jpeg,png|max:2048',
            'foto_gizi'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Tambahkan blok ini untuk memastikan nilai null menjadi 0
        $nutritionKeys = ['kalori', 'lemak_total', 'lemak_jenuh', 'protein', 'gula', 'karbohidrat', 'garam'];
        foreach ($nutritionKeys as $key) {
            if (empty($validated[$key])) {
                $validated[$key] = 0;
            }
        }

        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $namaFile = time() . '_' . uniqid() . '.' . $foto->getClientOriginalExtension();
                $path = $foto->storeAs('foto_produk', $namaFile);
                if (!$path) {
                    return back()->withErrors(['foto' => 'Gagal upload file!']);
                }
                $fotoPaths[] = 'storage/foto_produk/' . $namaFile;
            }
        }

        // Simpan foto gizi jika ada
        $fotoGiziPath = null;
        if ($request->hasFile('foto_gizi')) {
            $fotoGizi = $request->file('foto_gizi');
            $namaFileGizi = time() . '_gizi_' . uniqid() . '.' . $fotoGizi->getClientOriginalExtension();
            $pathGizi = $fotoGizi->storeAs('foto_gizi', $namaFileGizi);
            if ($pathGizi) {
                $fotoGiziPath = 'storage/foto_gizi/' . $namaFileGizi;
            }
        }

        $validated['foto'] = $fotoPaths;
        $validated['foto_gizi'] = $fotoGiziPath;
        $validated['status'] = 'approved';

        $product = Product::create($validated);

        // === LOG AKTIVITAS INPUT PRODUK OLEH ADMIN ===
        LogAktivitas::create([
            'user_id'   => Auth::id(),
            'role'      => Auth::user()->role ?? 'admin',
            'aksi'      => 'input_produk',
            'kategori'  => 'produk', // <=== ini ditambahkan
            'deskripsi' => 'Input produk: ' . $product->nama_produk . ' (Kode: ' . $product->kode_produk . ')',
            'ip_address'=> $request->ip(),
        ]);

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kode_produk'   => 'required|unique:product,kode_produk,' . $id,
            'nama_produk'   => 'required',
            'kategori'      => 'required|in:minuman,snack dan cemilan,makanan instan',
            'stock'         => 'required|integer',
            'harga'         => 'required|integer|min:0',
            'kalori'        => 'nullable|integer',
            'lemak_total'   => 'nullable|numeric',
            'lemak_jenuh'   => 'nullable|numeric',
            'protein'       => 'nullable|numeric',
            'gula'          => 'nullable|numeric',
            'karbohidrat'   => 'nullable|numeric',
            'garam'         => 'nullable|numeric',
            'foto.*'        => 'image|mimes:jpg,jpeg,png|max:2048',
            'foto_gizi'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Tambahkan blok ini untuk memastikan nilai null menjadi 0
        // dan menangani jika checkbox info gizi tidak dicentang
        $nutritionKeys = ['kalori', 'lemak_total', 'lemak_jenuh', 'protein', 'gula', 'karbohidrat', 'garam'];
        if ($request->has('add_nutrition_toggle')) {
            foreach ($nutritionKeys as $key) {
                if (empty($validated[$key])) {
                    $validated[$key] = 0;
                }
            }
        } else {
            // Jika checkbox tidak dicentang, reset semua nilai gizi ke 0
            foreach ($nutritionKeys as $key) {
                $validated[$key] = 0;
            }
        }

        $product = Product::findOrFail($id);

        // Simpan value sebelum update untuk log perubahan
        $oldData = $product->toArray();

        $fotoPaths = $product->foto ?? [];
        if ($request->hasFile('foto')) {
            // Hapus foto lama
            if (is_array($fotoPaths)) {
                foreach ($fotoPaths as $oldFoto) {
                    $oldPath = str_replace('storage/', 'public/', $oldFoto);
                    Storage::delete($oldPath);
                }
            }

            $fotoPaths = [];
            foreach ($request->file('foto') as $foto) {
                $namaFile = time() . '_' . uniqid() . '.' . $foto->getClientOriginalExtension();
                $path = $foto->storeAs('foto_produk', $namaFile);
                if (!$path) {
                    return back()->withErrors(['foto' => 'Gagal upload file!']);
                }
                $fotoPaths[] = 'storage/foto_produk/' . $namaFile;
            }
        }

        $validated['foto'] = $fotoPaths;

        // Handle foto gizi
        if ($request->hasFile('foto_gizi')) {
            // Hapus foto gizi lama
            if ($product->foto_gizi) {
                Storage::delete(str_replace('storage/', 'public/', $product->foto_gizi));
            }

            $fotoGizi = $request->file('foto_gizi');
            $namaFileGizi = time() . '_gizi_' . uniqid() . '.' . $fotoGizi->getClientOriginalExtension();
            $pathGizi = $fotoGizi->storeAs('foto_gizi', $namaFileGizi);
            if ($pathGizi) {
                $validated['foto_gizi'] = 'storage/foto_gizi/' . $namaFileGizi;
            }
        }

        $product->update($validated);

        // === LOG AKTIVITAS ADMIN ===
        // Deteksi perubahan field penting (misal: harga, stok, nama)
        $changes = [];
        foreach (['nama_produk', 'harga', 'stock', 'kategori'] as $field) {
            if ($oldData[$field] != $validated[$field]) {
                $changes[] = ucfirst(str_replace('_', ' ', $field)) . ': ' . $oldData[$field] . ' → ' . $validated[$field];
            }
        }
        $changesStr = $changes ? ' (' . implode('; ', $changes) . ')' : '';

        LogAktivitas::create([
            'user_id'   => Auth::id(),
            'role'      => Auth::user()->role ?? 'admin',
            'aksi'      => 'edit_produk',
            'kategori'  => 'produk', // <=== ini ditambahkan
            'deskripsi' => 'Edit produk: ' . $product->nama_produk . ' (Kode: ' . $product->kode_produk . ')' . $changesStr,
            'ip_address'=> $request->ip(),
        ]);

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil diperbarui.');
    }


    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Simpan nama dan kode produk untuk log sebelum hapus
        $nama_produk = $product->nama_produk;
        $kode_produk = $product->kode_produk;

        // Hapus foto produk jika ada
        if (is_array($product->foto)) {
            foreach ($product->foto as $fotoPath) {
                $path = str_replace('storage/', 'public/', $fotoPath);
                Storage::delete($path);
            }
        }

        // Hapus foto gizi jika ada
        if ($product->foto_gizi) {
            Storage::delete(str_replace('storage/', 'public/', $product->foto_gizi));
        }

        $product->delete();

        // LOG AKTIVITAS ADMIN
        LogAktivitas::create([
            'user_id'   => Auth::id(),
            'role'      => Auth::user()->role ?? 'admin',
            'aksi'      => 'hapus_produk',
            'kategori'  => 'produk', // <=== ini ditambahkan
            'deskripsi' => 'Hapus produk: ' . $nama_produk . ' (Kode: ' . $kode_produk . ')',
            'ip_address'=> request()->ip(),
        ]);

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil dihapus.');
    }
    public function getProduct()
    {
        $products = Product::all()->map(function ($product) {
            return [
                'id'            => $product->id,
                'kode_produk'   => $product->kode_produk,
                'nama_produk'   => $product->nama_produk,
                'kategori'      => $product->kategori,
                'harga'         => $product->harga,
                'kalori'        => $product->kalori,
                'lemak_total'   => $product->lemak_total,
                'lemak_jenuh'   => $product->lemak_jenuh,
                'protein'       => $product->protein,
                'gula'          => $product->gula,
                'karbohidrat'   => $product->karbohidrat,
                'garam'         => $product->garam,
                'foto'          => $product->foto,
                'status'        => $product->status,
                'foto_gizi'     => $product->foto_gizi,
                'stock'         => $product->stock,
                'persen_akg'    => $product->persenAkg(),
                'is_sehat'      => $product->isSehat(),
            ];
        });

        return response()->json($products);
    }

    public function storeByUser(Request $request)
    {
        $validated = $request->validate([
            'kode_produk' => 'required|unique:product,kode_produk',
            'nama_produk' => 'required',
            'kategori' => 'required|in:minuman,snack dan cemilan,makanan instan',
            'kalori' => 'required|integer',
            'lemak_total' => 'required|numeric',
            'lemak_jenuh' => 'required|numeric',
            'protein' => 'required|numeric',
            'gula' => 'required|numeric',
            'karbohidrat' => 'required|numeric',
            'garam' => 'required|numeric',
            'foto.*' => 'image|mimes:jpg,jpeg,png|max:2048',
            'foto_gizi' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $namaFile = time() . '_' . uniqid() . '.' . $foto->getClientOriginalExtension();
                $path = $foto->storeAs('foto_produk', $namaFile);
                if (!$path) {
                    return response()->json(['error' => 'Gagal upload foto!'], 500);
                }
                $fotoPaths[] = 'storage/foto_produk/' . $namaFile;
            }
        }

        $fotoGiziPath = null;
        if ($request->hasFile('foto_gizi')) {
            $fotoGizi = $request->file('foto_gizi');
            $namaFileGizi = time() . '_gizi_' . uniqid() . '.' . $fotoGizi->getClientOriginalExtension();
            $pathGizi = $fotoGizi->storeAs('foto_gizi', $namaFileGizi);
            if ($pathGizi) {
                $fotoGiziPath = 'storage/foto_gizi/' . $namaFileGizi;
            }
        }

        $validated['foto'] = $fotoPaths;
        $validated['foto_gizi'] = $fotoGiziPath;
        $validated['status'] = 'pending';

        Product::create($validated);

        return response()->json(['message' => 'Produk berhasil diajukan, menunggu persetujuan admin.'], 201);
    }

    public function getByKode($kode)
    {
        $product = Product::where('kode_produk', $kode)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'produk'      => $product,
            'persen_akg'  => $product->persenAkg(),
            'is_sehat'    => $product->isSehat(),
        ]);
    }

    public function extractTextFromImage(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        // Simpan gambar ke storage sementara
        $path = $request->file('foto')->store('ocr_gizi_tmp', 'public');
        $fullPath = storage_path('app/public/' . $path);

        // Jalankan OCR
        try {
            $ocr = (new TesseractOCR($fullPath))
                ->lang('ind') // Gunakan bahasa Indonesia jika sudah install. Kalau error, hapus baris ini.
                ->run();

            // Tambahkan LOG hasil OCR mentah ke Laravel log
            \Log::info('[OCR RAW]', ['ocr_raw' => $ocr]);
        } catch (\Exception $e) {
            @unlink($fullPath);
            \Log::error('Gagal proses OCR:', [
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Gagal proses OCR, cek log server!',
            ], 500);
        }
        // Hapus file temp!
        @unlink($fullPath);

        // Parsing hasil OCR menjadi array nilai gizi
        $parsed = $this->parseGiziText($ocr);

        return response()->json([
            'parsed_data' => $parsed,
        ]);
    }


/**
 * Parse hasil teks OCR dari label gizi menjadi array terstruktur.
 * Cakupan: kalori, lemak_total, lemak_jenuh, protein, gula, karbohidrat, garam.
 */
private function parseGiziText($text)
{
    $result = [
        'kalori'        => null,
        'lemak_total'   => null,
        'lemak_jenuh'   => null,
        'protein'       => null,
        'gula'          => null,
        'karbohidrat'   => null,
        'garam'         => null,
    ];

    // Ambil angka SETELAH keyword (ambil yang paling deket!)
    // regex toleran, ambil angka pertama setelah keyword
    $pairs = [
        'kalori'        => '/Energi Total.*?([0-9]+(?:[.,][0-9]+)?)/i',
        'lemak_total'   => '/Lemak Total.*?([0-9]+(?:[.,][0-9]+)?)/i',
        'lemak_jenuh'   => '/Lemak Jenuh.*?([0-9]+(?:[.,][0-9]+)?)/i',
        'protein'       => '/Protein.*?([0-9]+(?:[.,][0-9]+)?)/i',
        'karbohidrat'   => '/Karbohidrat.*?([0-9]+(?:[.,][0-9]+)?)/i',
        'gula'          => '/Gula.*?([0-9]+(?:[.,][0-9]+)?)/i',
        'garam'         => '/(?:Garam|Sodium|Natrium).*?([0-9]+(?:[.,][0-9]+)?)/i',
    ];

foreach ($pairs as $nutrisi => $regex) {
    if (preg_match($regex, $text, $m)) {
        // Kalau ada angka, ambil hanya angka pertama saja!
        preg_match('/([0-9]+(?:[.,][0-9]+)?)/', $m[0], $angka);
        $val = isset($angka[1]) ? str_replace(',', '.', $angka[1]) : 0;
        $result[$nutrisi] = is_numeric($val) ? $val : 0;
    }
}

// Jika null, isi 0
foreach ($result as $k => $v) {
    if ($k === 'garam') continue;
    // Koreksi angka terlalu besar akibat OCR salah baca (misal: 1890 → 18.9, 1495 → 14.95, 1396 → 13.96)
    if ($v > 1000 && $v < 20000) {
        // ambil dua digit pertama (misal: 1890 -> 18.90, 1495 -> 14.95)
        $fix = substr($v, 0, -2) . '.' . substr($v, -2);
        $fix = floatval($fix);
        if ($fix > 0 && $fix < 100) {
            $result[$k] = $fix;
        } else {
            $result[$k] = 0;
        }
    }
    // Koreksi juga jika 3 digit (misal 896 -> 8.96), tapi jangan kalori
    if ($v > 100 && $v < 1000 && $k !== 'kalori' && $k !== 'garam') {
        $fix = substr($v, 0, -2) . '.' . substr($v, -2);
        $fix = floatval($fix);
        if ($fix > 0 && $fix < 100) {
            $result[$k] = $fix;
        } else {
            $result[$k] = 0;
        }
    }
    // Jika null atau negatif, fallback ke 0
    if ($v === null || $v < 0) $result[$k] = 0;
}

    return $result;
}

}
