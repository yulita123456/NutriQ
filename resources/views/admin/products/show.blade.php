@extends('layouts.admin')

@section('header')
    <h1 class="text-3xl font-bold text-gray-800 mb-0">Detail Produk</h1>
    <p class="text-sm text-gray-600">Informasi lengkap untuk produk <span class="font-semibold">{{ $product->nama_produk }}</span>.</p>
@endsection

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="{{ route('admin.produk.index') }}"
               class="inline-flex items-center px-4 py-2 bg-white hover:bg-gray-100 text-gray-700 rounded-lg shadow-sm font-semibold transition mr-4 border border-gray-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="grid md:grid-cols-3">
                {{-- KOLOM KIRI: FOTO & AKSI --}}
                <div class="md:col-span-1 p-6 border-r border-gray-100 flex flex-col items-center text-center">
                    @if ($product->foto && is_array($product->foto) && count($product->foto))
                        <img src="{{ asset($product->foto[0]) }}" alt="Foto {{ $product->nama_produk }}" class="w-48 h-48 object-cover rounded-xl shadow-lg mb-4" />
                    @else
                        <div class="w-48 h-48 bg-gray-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-image fa-3x text-gray-300"></i>
                        </div>
                    @endif
                    <h2 class="text-2xl font-bold text-gray-800">{{ $product->nama_produk }}</h2>
                    <p class="text-sm text-gray-500 mb-4">{{ $product->kode_produk }}</p>

                    <div class="flex items-center gap-2 mt-4">
                        <a href="{{ route('admin.produk.edit', $product->id) }}" class="inline-flex items-center justify-center bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-5 rounded-lg transition shadow">
                            <i class="fas fa-edit mr-2"></i> Edit
                        </a>
                        <form action="{{ route('admin.produk.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus produk ini?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-5 rounded-lg transition shadow">
                                <i class="fas fa-trash-alt mr-2"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>

                {{-- KOLOM KANAN: DETAIL INFO --}}
                <div class="md:col-span-2 p-8">
                    {{-- INFO UTAMA --}}
                    <div class="border-b pb-5 mb-5">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Produk</h3>
                        {{-- GRID DIUBAH MENJADI 3 KOLOM AGAR RAPI --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-4 text-sm">
                            <div>
                                <label class="block text-gray-500">Kategori</label>
                                <p class="text-gray-800 font-semibold">{{ ucwords($product->kategori) }}</p>
                            </div>
                            <div>
                                <label class="block text-gray-500">Stock</label>
                                <p class="text-gray-800 font-semibold">{{ $product->stock }} pcs</p>
                            </div>
                            <div>
                                <label class="block text-gray-500">Harga</label>
                                <p class="text-green-600 font-bold text-base">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- INFO GIZI --}}
                    @if ($product->hasNutritionData())
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Nilai Gizi</h3>
                            <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-sm">
                                <div><label class="text-gray-500">Kalori:</label> <span class="font-semibold">{{ $product->kalori }} kkal</span></div>
                                <div><label class="text-gray-500">Karbohidrat:</label> <span class="font-semibold">{{ $product->karbohidrat }} g</span></div>
                                <div><label class="text-gray-500">Lemak Total:</label> <span class="font-semibold">{{ $product->lemak_total }} g</span></div>
                                <div><label class="text-gray-500">Lemak Jenuh:</label> <span class="font-semibold">{{ $product->lemak_jenuh }} g</span></div>
                                <div><label class="text-gray-500">Protein:</label> <span class="font-semibold">{{ $product->protein }} g</span></div>
                                <div><label class="text-gray-500">Gula:</label> <span class="font-semibold">{{ $product->gula }} g</span></div>
                                <div><label class="text-gray-500">Garam (Natrium):</label> <span class="font-semibold">{{ $product->garam }} mg</span></div>
                            </div>

                            @if (!empty($product->foto_gizi))
                                <div class="mt-6">
                                    <label class="block text-gray-600 font-semibold mb-2">Foto Label Gizi</label>
                                    <img src="{{ asset($product->foto_gizi) }}" alt="Foto Label Gizi" class="w-48 h-auto rounded-lg shadow border border-green-200" />
                                </div>
                            @endif
                        </div>
                    @else
                         <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Nilai Gizi</h3>
                         <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 p-4 rounded-lg" role="alert">
                             <p>Informasi nilai gizi untuk produk ini belum ditambahkan.</p>
                         </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
