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

            {{-- TAMPILKAN ERROR VALIDASI UMUM JIKA ADA --}}
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Terjadi Kesalahan!</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- INFORMASI DASAR PRODUK --}}
            <div class="border-b pb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dasar</h3>
                <div class="space-y-4">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">Kode Produk</label>
                            <input type="text" name="kode_produk" value="{{ old('kode_produk', $product->kode_produk) }}" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-green-300 shadow-sm @error('kode_produk') border-red-500 @enderror" required>
                            @error('kode_produk') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">Nama Produk</label>
                            <input type="text" name="nama_produk" value="{{ old('nama_produk', $product->nama_produk) }}" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-green-300 shadow-sm @error('nama_produk') border-red-500 @enderror" required>
                            @error('nama_produk') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid md:grid-cols-2 gap-6 items-end">
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">Kategori</label>
                            <select name="kategori" id="kategori" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-green-300 shadow-sm @error('kategori') border-red-500 @enderror" required>
                                <option value="minuman" {{ old('kategori', $product->kategori) == 'minuman' ? 'selected' : '' }}>Minuman</option>
                                <option value="snack dan cemilan" {{ old('kategori', $product->kategori) == 'snack dan cemilan' ? 'selected' : '' }}>Snack dan Cemilan</option>
                                <option value="makanan instan" {{ old('kategori', $product->kategori) == 'makanan instan' ? 'selected' : '' }}>Makanan Instan</option>
                            </select>
                            @error('kategori') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">Stock</label>
                            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-green-300 shadow-sm @error('stock') border-red-500 @enderror" required>
                            @error('stock') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">Harga (Rp)</label>
                            <input type="number" name="harga" value="{{ old('harga', $product->harga) }}" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-green-300 shadow-sm @error('harga') border-red-500 @enderror" required>
                            @error('harga') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-gray-700 font-medium">Foto Produk <span class="text-xs text-gray-400 font-normal">(abaikan jika tidak ganti)</span></label>
                            <label for="foto_produk" class="flex flex-col items-center justify-center h-36 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-green-400 transition bg-gray-50 @error('foto.*') border-red-500 @enderror">
                                <i class="fas fa-image fa-2x text-green-400"></i>
                                <span class="mt-1 text-sm text-gray-600">Klik untuk ganti foto produk</span>
                                <input type="file" name="foto[]" id="foto_produk" accept="image/*" multiple class="hidden" />
                            </label>
                            <div id="preview-produk" class="flex gap-3 mt-3">
                                {{-- ==================================================================== --}}
                                {{-- PERUBAHAN DI SINI --}}
                                {{-- ==================================================================== --}}
                                @if ($product->foto && is_array($product->foto))
                                    @foreach ($product->foto as $foto)
                                        <img src="{{ asset($foto) }}" alt="Foto Produk" class="w-20 h-20 object-cover rounded border border-gray-200 shadow">
                                    @endforeach
                                @endif
                                {{-- ==================================================================== --}}
                            </div>
                             @error('foto.*') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                             <span class="block mt-1 text-xs text-gray-500 leading-relaxed">
                                 <strong>Ukuran file foto maksimal 2MB.</strong>
                            </span>
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
                    <label for="foto_gizi" class="flex flex-col items-center justify-center h-32 border-2 border-dashed border-green-400 rounded-lg cursor-pointer hover:border-green-500 transition bg-green-50 @error('foto_gizi') border-red-500 @enderror">
                        <i class="fas fa-seedling fa-lg text-green-500"></i>
                        <span class="mt-1 text-sm text-green-700">Klik untuk ganti foto label gizi</span>
                        <input type="file" name="foto_gizi" id="foto_gizi" accept="image/*" class="hidden" />
                    </label>
                    <div id="preview-gizi" class="flex gap-3 mt-3">
                        {{-- ==================================================================== --}}
                        {{-- PERUBAHAN DI SINI --}}
                        {{-- ==================================================================== --}}
                        @if (!empty($product->foto_gizi))
                            <img src="{{ asset($product->foto_gizi) }}" alt="Foto Gizi" class="w-20 h-20 object-cover rounded border border-green-200 shadow">
                        @endif
                        {{-- ==================================================================== --}}
                    </div>
                    @error('foto_gizi') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    <span class="block mt-1 text-xs text-gray-500 leading-relaxed">
                        <strong>Tips Mengunggah Label Gizi:</strong><br>
                        - Pastikan foto label diambil dengan <b>kamera yang jelas &amp; tidak blur</b>.<br>
                        - Pastikan <b>seluruh tulisan pada label gizi dapat terbaca</b>.<br>
                        <strong class="text-yellow-600">Disclaimer:</strong><br>
                        Fitur pembacaan otomatis (OCR) <b>masih dalam tahap pengembangan</b>. Mohon untuk <b>selalu memeriksa dan memastikan hasil isian data gizi sudah sesuai.</b>
                    </span>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    <div class="nutrition-field-always-show">
                        <label class="block text-gray-700 mb-2 font-medium">Kalori (kkal)</label>
                        <input type="number" name="kalori" id="kalori" value="{{ old('kalori', $product->kalori) }}" class="w-full border border-gray-300 px-3 py-2 rounded shadow-sm @error('kalori') border-red-500 @enderror">
                        @error('kalori') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="nutrition-field-always-show">
                        <label class="block text-gray-700 mb-2 font-medium">Karbohidrat (g)</label>
                        <input type="number" step="0.1" name="karbohidrat" id="karbohidrat" value="{{ old('karbohidrat', $product->karbohidrat) }}" class="w-full border border-gray-300 px-3 py-2 rounded shadow-sm @error('karbohidrat') border-red-500 @enderror">
                        @error('karbohidrat') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="nutrition-field-always-show">
                        <label class="block text-gray-700 mb-2 font-medium">Gula (g)</label>
                        <input type="number" step="0.1" name="gula" id="gula" value="{{ old('gula', $product->gula) }}" class="w-full border border-gray-300 px-3 py-2 rounded shadow-sm @error('gula') border-red-500 @enderror">
                        @error('gula') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="nutrition-field-always-show">
                        <label class="block text-gray-700 mb-2 font-medium">Garam (mg)</label>
                        <input type="number" step="0.1" name="garam" id="garam" value="{{ old('garam', $product->garam) }}" class="w-full border border-gray-300 px-3 py-2 rounded shadow-sm @error('garam') border-red-500 @enderror">
                        @error('garam') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="nutrition-field-optional">
                        <label class="block text-gray-700 mb-2 font-medium">Lemak Total (g)</label>
                        <input type="number" step="0.1" name="lemak_total" id="lemak_total" value="{{ old('lemak_total', $product->lemak_total) }}" class="w-full border border-gray-300 px-3 py-2 rounded shadow-sm @error('lemak_total') border-red-500 @enderror">
                        @error('lemak_total') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="nutrition-field-optional">
                        <label class="block text-gray-700 mb-2 font-medium">Lemak Jenuh (g)</label>
                        <input type="number" step="0.1" name="lemak_jenuh" id="lemak_jenuh" value="{{ old('lemak_jenuh', $product->lemak_jenuh) }}" class="w-full border border-gray-300 px-3 py-2 rounded shadow-sm @error('lemak_jenuh') border-red-500 @enderror">
                        @error('lemak_jenuh') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="nutrition-field-optional">
                        <label class="block text-gray-700 mb-2 font-medium">Protein (g)</label>
                        <input type="number" step="0.1" name="protein" id="protein" value="{{ old('protein', $product->protein) }}" class="w-full border border-gray-300 px-3 py-2 rounded shadow-sm @error('protein') border-red-500 @enderror">
                        @error('protein') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
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

    // Cek jika produk sudah punya data gizi (salah satu field tidak 0 atau null), maka centang checkbox utama
    // Pastikan nilai 'null' atau string kosong juga dianggap tidak ada data.
    const hasNutritionData = {{
        ($product->kalori || $product->lemak_total || $product->lemak_jenuh || $product->protein || $product->gula || $product->karbohidrat || $product->garam) ? 'true' : 'false'
    }};

    if (hasNutritionData) {
        addNutritionToggle.checked = true;
    }

    // Set nilai checkbox showAllNutrition saat halaman dimuat berdasarkan apakah ada data opsional yang terisi
    const hasOptionalNutritionData = {{
        ($product->lemak_total || $product->lemak_jenuh || $product->protein) ? 'true' : 'false'
    }};

    if (hasOptionalNutritionData) {
        showAllCheckbox.checked = true;
    }


    function updateFormVisibility() {
        if (addNutritionToggle.checked) {
            nutritionSection.classList.remove('hidden');
        } else {
            nutritionSection.classList.add('hidden');
            showAllCheckbox.checked = false; // Reset checkbox jika section disembunyikan
        }

        const selectedKategori = kategoriSelect.value;

        // Tampilkan checkbox "Tampilkan semua kolom gizi" hanya jika kategori "minuman" dan toggle gizi aktif
        if (selectedKategori === 'minuman' && addNutritionToggle.checked) {
            showAllCheckboxWrapper.classList.remove('hidden');
        } else {
            showAllCheckboxWrapper.classList.add('hidden');
            showAllCheckbox.checked = false; // Reset checkbox jika tidak relevan
        }

        // Tampilkan/sembunyikan kolom opsional berdasarkan kategori dan checkbox "tampilkan semua"
        if (selectedKategori === 'minuman' && !showAllCheckbox.checked) {
            optionalFields.forEach(field => field.classList.add('hidden'));
        } else {
            optionalFields.forEach(field => field.classList.remove('hidden'));
        }
    }

    // Event listeners
    addNutritionToggle.addEventListener('change', updateFormVisibility);
    kategoriSelect.addEventListener('change', updateFormVisibility);
    showAllCheckbox.addEventListener('change', updateFormVisibility);

    // Initial call to set correct visibility based on initial state/old input
    updateFormVisibility();

    // ===== Auto preview Foto Produk (multiple) =====
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

    // ===== Foto Gizi (OCR processing) =====
    inputGizi.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('foto', file);

        fetch('{{ route("produk.extractText") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.parsed_data) {
                document.getElementById('kalori').value = data.parsed_data.kalori || '';
                document.getElementById('lemak_total').value = data.parsed_data.lemak_total || '';
                document.getElementById('lemak_jenuh').value = data.parsed_data.lemak_jenuh || '';
                document.getElementById('protein').value = data.parsed_data.protein || '';
                document.getElementById('gula').value = data.parsed_data.gula || '';
                document.getElementById('karbohidrat').value = data.parsed_data.karbohidrat || '';
                document.getElementById('garam').value = data.parsed_data.garam || '';
            } else {
                alert('Gagal mengekstrak data. Silakan periksa gambar label gizi.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('❌ Gagal memproses gambar label gizi.');
        });
    });

    // Converter koma ke titik
    const decimalFields = ['kalori', 'lemak_total', 'lemak_jenuh', 'protein', 'gula', 'karbohidrat', 'garam'];
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
