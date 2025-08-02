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

        if ($request->filled('search')) {
            $query->where('nama_produk', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('harga_min')) {
            $query->where('harga', '>=', $request->harga_min);
        }

        if ($request->filled('harga_max')) {
            $query->where('harga', '<=', $request->harga_max);
        }

        $products = $query->paginate(15);

        return view('admin.products.index', compact('products'));
    }
    public function create()
    {
        return view('admin.products.create');
    }

/**
 * Menyimpan produk baru yang diinput oleh Admin dengan logging detail.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\RedirectResponse
 */
public function storeByAdmin(Request $request)
{
    // Mengarahkan log ke channel 'debuglog' yang akan menulis ke file 'storage/logs/debug.log'
    Log::channel('debuglog')->info('--- Memulai storeByAdmin ---');

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
        Log::channel('debuglog')->info('[FOTO PRODUK] Request memiliki file `foto`.');
        foreach ($request->file('foto') as $index => $foto) {
            if (!$foto->isValid()) {
                Log::channel('debuglog')->error("[FOTO PRODUK - File #{$index}] File tidak valid. Error: " . $foto->getError());
                continue;
            }

            // Ini adalah bagian paling penting
            $path = $foto->store('foto_produk', 'public');
            Log::channel('debuglog')->info("[FOTO PRODUK - File #{$index}] Hasil dari store(): " . ($path ?: 'GAGAL'));

            if ($path) {
                $fotoPaths[] = 'storage/' . $path;
            } else {
                Log::channel('debuglog')->error("[FOTO PRODUK - File #{$index}] Gagal menyimpan file ke storage.");
            }
        }
    }

    $fotoGiziPath = null;
    if ($request->hasFile('foto_gizi')) {
        Log::channel('debuglog')->info('[FOTO GIZI] Request memiliki file `foto_gizi`.');
        $fotoGizi = $request->file('foto_gizi');
        if ($fotoGizi->isValid()) {
            $pathGizi = $fotoGizi->store('foto_gizi', 'public');
            Log::channel('debuglog')->info("[FOTO GIZI] Hasil dari store(): " . ($pathGizi ?: 'GAGAL'));
            if ($pathGizi) {
                $fotoGiziPath = 'storage/' . $pathGizi;
            }
        } else {
            Log::channel('debuglog')->error('[FOTO GIZI] File tidak valid. Error: ' . $fotoGizi->getError());
        }
    }

    $validated['foto'] = $fotoPaths;
    $validated['foto_gizi'] = $fotoGiziPath;
    $validated['status'] = 'approved';

    $product = Product::create($validated);
    Log::channel('debuglog')->info('Produk baru berhasil dibuat di database dengan ID: ' . $product->id);

    LogAktivitas::create([
        'user_id'   => Auth::id(),
        'role'      => Auth::user()->role ?? 'admin',
        'aksi'      => 'input_produk',
        'kategori'  => 'produk',
        'deskripsi' => 'Input produk: ' . $product->nama_produk . ' (Kode: ' . $product->kode_produk . ')',
        'ip_address'=> $request->ip(),
    ]);

    // Menambahkan dua baris baru untuk spasi antar log agar mudah dibaca
    Log::channel('debuglog')->info('--- Selesai storeByAdmin ---' . "\n\n");
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
                    Storage::disk('public')->delete($oldFoto);
                    Log::info('Menghapus foto lama: ' . $oldFoto);
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
                Storage::disk('public')->delete($product->foto_gizi);
                Log::info('Menghapus foto gizi lama: ' . $product->foto_gizi);
            }
            $fotoGizi = $request->file('foto_gizi');
            $pathGizi = $fotoGizi->store('foto_gizi', 'public');
            Log::info('Path file gizi baru yang disimpan: ' . $pathGizi);
            if ($pathGizi) {
                $validated['foto_gizi'] = 'uploads/foto_gizi/' . basename($pathGizi);
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

        $nama_produk = $product->nama_produk;
        $kode_produk = $product->kode_produk;

        if (is_array($product->foto)) {
            foreach ($product->foto as $fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
        }

        if ($product->foto_gizi) {
            Storage::disk('public')->delete($product->foto_gizi);
        }

        $product->delete();

        LogAktivitas::create([
            'user_id'   => Auth::id(),
            'role'      => Auth::user()->role ?? 'admin',
            'aksi'      => 'hapus_produk',
            'kategori'  => 'produk',
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
                $path = $foto->store('foto_produk', 'public');
                if (!$path) {
                    return response()->json(['error' => 'Gagal upload foto!'], 500);
                }
                $fotoPaths[] = 'storage/' . $path;
            }
        }

        $fotoGiziPath = null;
        if ($request->hasFile('foto_gizi')) {
            $fotoGizi = $request->file('foto_gizi');
            $pathGizi = $fotoGizi->store('foto_gizi', 'public');
            if ($pathGizi) {
                $fotoGiziPath = 'storage/' . $pathGizi;
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
            'produk'        => $product,
            'persen_akg'    => $product->persenAkg(),
            'is_sehat'      => $product->isSehat(),
        ]);
    }

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
                ->psm(6)
                ->oem(1)
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

                if ($nutrisi === 'lemak_jenuh' && $value === 25.0 && strpos($text, 'lemak jenuh') !== false) {
                    $result[$nutrisi] = 2.5;
                } else {
                    $result[$nutrisi] = $value;
                }

                \Log::info("Parsed $nutrisi: " . $result[$nutrisi]);
            }
        }

        foreach ($result as $k => $v) {
            if ($v === null || $v < 0) {
                $result[$k] = 0;
            }
        }

        return $result;
    }
}
