@extends('layouts.admin')

@section('header')
    <h1 class="text-3xl font-bold text-gray-800 mb-0">Edit Produk</h1>
@endsection

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg p-8 border border-gray-100">
        <div class="flex items-center mb-8">
            <a href="{{ route('admin.produk.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded shadow font-semibold transition mr-3">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
        <form action="{{ route('admin.produk.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- INFORMASI DASAR PRODUK --}}
            <div class="border-b pb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dasar</h3>
                <div class="space-y-4">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">Kode Produk</label>
                            <input type="text" name="kode_produk" value="{{ old('kode_produk', $product->kode_produk) }}" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-green-300 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">Nama Produk</label>
                            <input type="text" name="nama_produk" value="{{ old('nama_produk', $product->nama_produk) }}" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-green-300 shadow-sm" required>
                        </div>
                    </div>
                    <div class="grid md:grid-cols-2 gap-6 items-end">
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">Kategori</label>
                            <select name="kategori" id="kategori" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-green-300 shadow-sm" required>
                                <option value="minuman" {{ old('kategori', $product->kategori) == 'minuman' ? 'selected' : '' }}>Minuman</option>
                                <option value="snack dan cemilan" {{ old('kategori', $product->kategori) == 'snack dan cemilan' ? 'selected' : '' }}>Snack dan Cemilan</option>
                                <option value="makanan instan" {{ old('kategori', $product->kategori) == 'makanan instan' ? 'selected' : '' }}>Makanan Instan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">Stock</label>
                            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-green-300 shadow-sm" required>
                        </div>
                    </div>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">Harga (Rp)</label>
                            <input type="number" name="harga" value="{{ old('harga', $product->harga) }}" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-green-300 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block mb-2 text-gray-700 font-medium">Foto Produk <span class="text-xs text-gray-400 font-normal">(abaikan jika tidak ganti)</span></label>
                            <label for="foto_produk" class="flex flex-col items-center justify-center h-36 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-green-400 transition bg-gray-50">
                                <i class="fas fa-image fa-2x text-green-400"></i>
                                <span class="mt-1 text-sm text-gray-600">Klik untuk ganti foto produk</span>
                                <input type="file" name="foto[]" id="foto_produk" accept="image/*" multiple class="hidden" />
                            </label>
                            <div id="preview-produk" class="flex gap-3 mt-3">
                                @if ($product->foto && is_array($product->foto))
                                    @foreach ($product->foto as $foto)
                                        <img src="{{ asset($foto) }}" alt="Foto Produk" class="w-20 h-20 object-cover rounded border border-gray-200 shadow">
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CHECKBOX UTAMA UNTUK MENAMPILKAN FORM GIZI --}}
            <div>
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" id="add-nutrition-toggle" name="add_nutrition_toggle" class="h-5 w-5 rounded text-green-500 focus:ring-green-400">
                    <span class="font-semibold text-gray-800 text-lg">Ubah Informasi Nilai Gizi?</span>
                </label>
            </div>

            {{-- SELURUH BAGIAN GIZI (AWALNYA TERSEMBUNYI) --}}
            <div id="nutrition-section" class="hidden space-y-6">
                <div id="show-all-nutrition-wrapper" class="hidden">
                    <label class="flex items-center space-x-2 text-sm text-gray-600">
                      <input type="checkbox" id="show-all-nutrition" class="rounded text-green-500 focus:ring-green-400">
                      <span>Tampilkan semua kolom gizi (opsional)</span>
                    </label>
                </div>
                <div>
                    <label class="block mb-2 text-gray-700 font-medium">Foto Label Gizi <span class="text-xs text-gray-400 font-normal">(abaikan jika tidak ganti)</span></label>
                    <label for="foto_gizi" class="flex flex-col items-center justify-center h-32 border-2 border-dashed border-green-400 rounded-lg cursor-pointer hover:border-green-500 transition bg-green-50">
                        <i class="fas fa-seedling fa-lg text-green-500"></i>
                        <span class="mt-1 text-sm text-green-700">Klik untuk ganti foto label gizi</span>
                        <input type="file" name="foto_gizi" id="foto_gizi" accept="image/*" class="hidden" />
                    </label>
                    <div id="preview-gizi" class="flex gap-3 mt-3">
                        @if (!empty($product->foto_gizi))
                            <img src="{{ asset($product->foto_gizi) }}" alt="Foto Gizi" class="w-20 h-20 object-cover rounded border border-green-200 shadow">
                        @endif
                    </div>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    <div class="nutrition-field-all">
                        <label class="block text-gray-700 mb-2 font-medium">Kalori (kkal)</label>
                        <input type="number" name="kalori" id="kalori" value="{{ old('kalori', $product->kalori) }}" class="w-full border border-gray-300 px-3 py-2 rounded shadow-sm">
                    </div>
                    <div class="nutrition-field-all">
                        <label class="block text-gray-700 mb-2 font-medium">Karbohidrat (g)</label>
                        <input type="number" step="0.1" name="karbohidrat" id="karbohidrat" value="{{ old('karbohidrat', $product->karbohidrat) }}" class="w-full border border-gray-300 px-3 py-2 rounded shadow-sm">
                    </div>
                    <div class="nutrition-field-all">
                        <label class="block text-gray-700 mb-2 font-medium">Gula (g)</label>
                        <input type="number" step="0.1" name="gula" id="gula" value="{{ old('gula', $product->gula) }}" class="w-full border border-gray-300 px-3 py-2 rounded shadow-sm">
                    </div>
                    <div class="nutrition-field-all">
                        <label class="block text-gray-700 mb-2 font-medium">Garam (mg)</label>
                        <input type="number" step="0.1" name="garam" id="garam" value="{{ old('garam', $product->garam) }}" class="w-full border border-gray-300 px-3 py-2 rounded shadow-sm">
                    </div>
                    <div class="nutrition-field-optional">
                        <label class="block text-gray-700 mb-2 font-medium">Lemak Total (g)</label>
                        <input type="number" step="0.1" name="lemak_total" id="lemak_total" value="{{ old('lemak_total', $product->lemak_total) }}" class="w-full border border-gray-300 px-3 py-2 rounded shadow-sm">
                    </div>
                    <div class="nutrition-field-optional">
                        <label class="block text-gray-700 mb-2 font-medium">Lemak Jenuh (g)</label>
                        <input type="number" step="0.1" name="lemak_jenuh" id="lemak_jenuh" value="{{ old('lemak_jenuh', $product->lemak_jenuh) }}" class="w-full border border-gray-300 px-3 py-2 rounded shadow-sm">
                    </div>
                    <div class="nutrition-field-optional">
                        <label class="block text-gray-700 mb-2 font-medium">Protein (g)</label>
                        <input type="number" step="0.1" name="protein" id="protein" value="{{ old('protein', $product->protein) }}" class="w-full border border-gray-300 px-3 py-2 rounded shadow-sm">
                    </div>
                </div>
            </div>

            <div class="text-right pt-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg shadow transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addNutritionToggle = document.getElementById('add-nutrition-toggle');
    const nutritionSection = document.getElementById('nutrition-section');
    const kategoriSelect = document.getElementById('kategori');
    const showAllCheckboxWrapper = document.getElementById('show-all-nutrition-wrapper');
    const showAllCheckbox = document.getElementById('show-all-nutrition');
    const optionalFields = document.querySelectorAll('.nutrition-field-optional');

    // Cek jika produk sudah punya data gizi (bukan 0), maka centang checkbox utama
    const hasNutritionData = {{ $product->kalori > 0 || $product->lemak_total > 0 || $product->protein > 0 ? 'true' : 'false' }};
    if (hasNutritionData) {
        addNutritionToggle.checked = true;
    }

    function updateFormVisibility() {
        if (addNutritionToggle.checked) {
            nutritionSection.classList.remove('hidden');
        } else {
            nutritionSection.classList.add('hidden');
            showAllCheckbox.checked = false;
        }

        const selectedKategori = kategoriSelect.value;

        if (selectedKategori === 'minuman' && addNutritionToggle.checked) {
            showAllCheckboxWrapper.classList.remove('hidden');
        } else {
            showAllCheckboxWrapper.classList.add('hidden');
        }

        if (selectedKategori === 'minuman' && !showAllCheckbox.checked) {
            optionalFields.forEach(field => field.classList.add('hidden'));
        } else {
            optionalFields.forEach(field => field.classList.remove('hidden'));
        }
    }

    addNutritionToggle.addEventListener('change', updateFormVisibility);
    kategoriSelect.addEventListener('change', updateFormVisibility);
    showAllCheckbox.addEventListener('change', updateFormVisibility);
    updateFormVisibility();

    // Script preview gambar (sama seperti di create.blade.php)
    const inputProduk = document.getElementById('foto_produk');
    const previewProduk = document.getElementById('preview-produk');
    inputProduk.addEventListener('change', function () {
        previewProduk.innerHTML = ''; // Kosongkan preview lama
        if (inputProduk.files.length > 0) {
            Array.from(inputProduk.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    let img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = "w-20 h-20 object-cover rounded border border-gray-200 shadow";
                    previewProduk.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }
    });

    const inputGizi = document.getElementById('foto_gizi');
    const previewGizi = document.getElementById('preview-gizi');
    inputGizi.addEventListener('change', function () {
        previewGizi.innerHTML = '';
        if (inputGizi.files.length > 0) {
            const file = inputGizi.files[0];
            const reader = new FileReader();
            reader.onload = e => {
                let img = document.createElement('img');
                img.src = e.target.result;
                img.className = "w-20 h-20 object-cover rounded border border-green-200 shadow";
                previewGizi.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });

    // Converter koma ke titik
    const decimalFields = ['lemak_total', 'lemak_jenuh', 'protein', 'gula', 'karbohidrat', 'garam'];
    decimalFields.forEach(function(field) {
        const input = document.getElementById(field);
        if (input) {
            input.addEventListener('input', function (e) {
                this.value = this.value.replace(/,/g, '.');
            });
        }
    });
});
</script>
@endpush
@endsection
