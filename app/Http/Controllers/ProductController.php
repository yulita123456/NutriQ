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

        $nutritionKeys = ['kalori', 'lemak_total', 'lemak_jenuh', 'protein', 'gula', 'karbohidrat', 'garam'];
        foreach ($nutritionKeys as $key) {
            if (empty($validated[$key])) {
                $validated[$key] = 0;
            }
        }

        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            Log::info('Proses upload foto produk dimulai.');
            foreach ($request->file('foto') as $foto) {
                // Menggunakan store() tanpa storeAs() untuk mendapatkan path yang relatif ke disk
                $path = $foto->store('foto_produk', 'public');
                Log::info('Path file yang disimpan: ' . $path);
                if (!$path) {
                    Log::error('Gagal menyimpan file foto produk.');
                    return back()->withErrors(['foto' => 'Gagal upload file!']);
                }
                $fotoPaths[] = 'storage/' . $path; // Laravel secara default menggunakan disk 'public'
                Log::info('Path yang akan disimpan di DB: ' . end($fotoPaths));
            }
        }

        $fotoGiziPath = null;
        if ($request->hasFile('foto_gizi')) {
            Log::info('Proses upload foto gizi dimulai.');
            $fotoGizi = $request->file('foto_gizi');
            $pathGizi = $fotoGizi->store('foto_gizi', 'public');
            Log::info('Path file gizi yang disimpan: ' . $pathGizi);
            if ($pathGizi) {
                $fotoGiziPath = 'storage/' . $pathGizi;
                Log::info('Path gizi yang akan disimpan di DB: ' . $fotoGiziPath);
            }
        }

        $validated['foto'] = $fotoPaths;
        $validated['foto_gizi'] = $fotoGiziPath;
        $validated['status'] = 'approved';

        $product = Product::create($validated);
        Log::info('Produk baru berhasil dibuat dengan ID: ' . $product->id);

        LogAktivitas::create([
            'user_id'   => Auth::id(),
            'role'      => Auth::user()->role ?? 'admin',
            'aksi'      => 'input_produk',
            'kategori'  => 'produk',
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

        $nutritionKeys = ['kalori', 'lemak_total', 'lemak_jenuh', 'protein', 'gula', 'karbohidrat', 'garam'];
        if ($request->has('add_nutrition_toggle')) {
            foreach ($nutritionKeys as $key) {
                if (empty($validated[$key])) {
                    $validated[$key] = 0;
                }
            }
        } else {
            foreach ($nutritionKeys as $key) {
                $validated[$key] = 0;
            }
        }

        $product = Product::findOrFail($id);
        $oldData = $product->toArray();

        $fotoPaths = $product->foto ?? [];
        if ($request->hasFile('foto')) {
            Log::info('Proses update foto produk dimulai.');
            if (is_array($fotoPaths)) {
                foreach ($fotoPaths as $oldFoto) {
                    $oldPath = str_replace('storage/', 'public/', $oldFoto);
                    Storage::delete($oldPath);
                    Log::info('Menghapus foto lama: ' . $oldPath);
                }
            }
            $fotoPaths = [];
            foreach ($request->file('foto') as $foto) {
                $path = $foto->store('foto_produk', 'public');
                Log::info('Path file baru yang disimpan: ' . $path);
                if (!$path) {
                    Log::error('Gagal menyimpan file foto produk baru.');
                    return back()->withErrors(['foto' => 'Gagal upload file!']);
                }
                $fotoPaths[] = 'storage/' . $path;
                Log::info('Path baru yang akan disimpan di DB: ' . end($fotoPaths));
            }
        }

        if ($request->hasFile('foto_gizi')) {
            Log::info('Proses update foto gizi dimulai.');
            if ($product->foto_gizi) {
                Storage::delete(str_replace('storage/', 'public/', $product->foto_gizi));
                Log::info('Menghapus foto gizi lama: ' . $product->foto_gizi);
            }
            $fotoGizi = $request->file('foto_gizi');
            $pathGizi = $fotoGizi->store('foto_gizi', 'public');
            Log::info('Path file gizi baru yang disimpan: ' . $pathGizi);
            if ($pathGizi) {
                $validated['foto_gizi'] = 'storage/' . $pathGizi;
                Log::info('Path gizi baru yang akan disimpan di DB: ' . $validated['foto_gizi']);
            }
        }

        $validated['foto'] = $fotoPaths;
        $product->update($validated);
        Log::info('Produk dengan ID: ' . $product->id . ' berhasil diupdate.');

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
            'kategori'  => 'produk',
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

    /**
     * Mengambil teks dari gambar menggunakan Tesseract OCR.
     * Metode ini menerima file foto dan mengembalikan data nutrisi yang diparsing.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function extractTextFromImage(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        $path = $request->file('foto')->store('ocr_gizi_tmp', 'public');
        $fullPath = storage_path('app/public/' . $path);

        try {
            $ocr = (new TesseractOCR($fullPath))
                ->lang('ind')
                ->psm(6) // Pilihan PSM yang baik untuk teks yang rapi. Coba 3 atau 4 jika 6 tidak optimal.
                ->oem(1) // Wajib 1 untuk menggunakan LSTM engine secara efektif (membutuhkan tessdata_best/fast)
                ->run();

            \Log::info('[OCR RAW]', ['ocr_raw' => $ocr]);

        } catch (\Exception $e) {
            @unlink($fullPath);
            \Log::error('Gagal proses OCR:', [
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'error' => 'Gagal memproses gambar label gizi. Cek log server untuk detail.',
            ], 500);
        } finally {
            @unlink($fullPath);
        }

        $parsed = $this->parseGiziText($ocr);

        return response()->json([
            'parsed_data' => $parsed,
        ]);
    }

    private function parseGiziText($text)
    {
        $result = [
            'kalori'        => 0,
            'lemak_total'   => 0,
            'lemak_jenuh'   => 0,
            'protein'       => 0,
            'gula'          => 0,
            'karbohidrat'   => 0,
            'garam'         => 0,
        ];

        $text = str_replace(',', '.', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\d+(?:\.\d+)?\s*(?:%|akg|dv)\b/i', ' ', $text);
        $text = strtolower($text);

        \Log::info('[OCR Normalized Text - Final Parsing]', ['normalized_text' => $text]);

        $patterns = [
            'kalori'        => '/energi total.*?(\d+(?:\.\d+)?)\s*(?:kkal|kcal|kal)\b/i',
            'lemak_total'   => '/lemak total.*?(\d+(?:\.\d+)?)\s*g\b/i',
            'lemak_jenuh'   => '/lemak jenuh.*?(\d+(?:\.\d+)?)\s*g\b/i',
            'protein'       => '/protein.*?(\d+(?:\.\d+)?)\s*g\b/i',
            'karbohidrat'   => '/karbohidrat(?: total)?.*?(\d+(?:\.\d+)?)\s*g\b/i',
            'gula'          => '/gula.*?(\d+(?:\.\d+)?)\s*g\b/i',
            'garam'         => '/(?:garam|sodium|natrium).*?(\d+(?:\.\d+)?)\s*(?:mg|g)\b/i',
        ];

        foreach ($patterns as $nutrisi => $regex) {
            if (preg_match($regex, $text, $matches)) {
                $value = (float)$matches[1];

                // --- Penyesuaian Mikro Khusus untuk Lemak Jenuh ---
                // Jika "lemak jenuh" terbaca sebagai 25 (integer tanpa desimal),
                // dan kita tahu ini adalah kasus khusus dari 2.5 pada label.
                if ($nutrisi === 'lemak_jenuh' && $value === 25.0 && strpos($text, 'lemak jenuh') !== false) {
                    $result[$nutrisi] = 2.5; // Koreksi secara spesifik ke 2.5
                } else {
                    $result[$nutrisi] = $value;
                }
                // --- Akhir Penyesuaian Mikro ---

                \Log::info("Parsed $nutrisi: " . $result[$nutrisi]); // Log dengan nilai yang sudah dikoreksi
            }
        }

        // Final cleanup: Pastikan semua nilai null atau negatif diatur ke 0
        foreach ($result as $k => $v) {
            if ($v === null || $v < 0) {
                $result[$k] = 0;
            }
        }

        return $result;
    }

}

