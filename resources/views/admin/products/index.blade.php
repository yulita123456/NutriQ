@extends('layouts.admin')
@section('header')
    <h1 class="text-3xl font-bold text-gray-800 mb-0">Daftar Produk</h1>
@endsection
@section('content')
<div class="py-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-7">
        <div>
            <p class="text-gray-500">Kelola seluruh produk kemasan aplikasi NutriQ.</p>
        </div>
        <a href="{{ route('admin.produk.create') }}"
           class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg shadow transition focus:outline-none">
            <i class="fas fa-plus"></i> Tambah Produk Baru
        </a>
    </div>

    {{-- Notifikasi Sukses --}}
        @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow" role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
            <div class="flex">
                <div class="py-1"><i class="fas fa-check-circle fa-2x text-green-500 mr-4"></i></div>
                <div>
                    <p class="font-bold">Sukses!</p>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Filter/Search Form --}}
    <form method="GET" class="bg-white rounded-xl shadow border border-gray-100 mb-5 p-4 md:p-5 md:grid md:grid-cols-6 md:gap-4 flex flex-col gap-3">
        <div class="flex flex-col">
            <label for="search" class="block text-sm text-gray-600 mb-1">Cari Nama Produk</label>
            <input type="text" id="search" name="search" value="{{ request('search') }}"
                placeholder="Cari produk..."
                class="border border-gray-300 rounded px-3 py-2 text-sm focus:ring focus:ring-green-100">
        </div>
        <div class="flex flex-col">
            <label for="kategori" class="block text-sm text-gray-600 mb-1">Kategori</label>
            <select id="kategori" name="kategori" class="border border-gray-300 rounded px-3 py-2 text-sm">
                <option value="" {{ request('kategori') == '' ? 'selected' : '' }}>Semua</option>
                <option value="minuman" {{ request('kategori') == 'minuman' ? 'selected' : '' }}>Minuman</option>
                <option value="snack dan cemilan" {{ request('kategori') == 'snack dan cemilan' ? 'selected' : '' }}>Snack dan Cemilan</option>
                <option value="makanan instan" {{ request('kategori') == 'makanan instan' ? 'selected' : '' }}>Makanan Instan</option>
            </select>
        </div>
        <div class="flex flex-col">
            <label for="harga_min" class="block text-sm text-gray-600 mb-1">Harga Min</label>
            <input type="number" id="harga_min" name="harga_min" value="{{ request('harga_min') }}"
                placeholder="Min"
                class="border border-gray-300 rounded px-3 py-2 text-sm">
        </div>
        <div class="flex flex-col">
            <label for="harga_max" class="block text-sm text-gray-600 mb-1">Harga Max</label>
            <input type="number" id="harga_max" name="harga_max" value="{{ request('harga_max') }}"
                placeholder="Max"
                class="border border-gray-300 rounded px-3 py-2 text-sm">
        </div>
        <div class="flex flex-col justify-end">
            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg shadow">
                Filter
            </button>
        </div>
        <div class="flex flex-col justify-end">
            <a href="{{ route('admin.produk.index') }}"
                class="text-sm text-gray-600 underline hover:text-green-600 transition">
                Reset
            </a>
        </div>
    </form>

    {{-- Table Produk --}}
    <div class="bg-white rounded-xl shadow border border-gray-100 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 font-semibold text-gray-600 text-sm text-left">Kode Produk</th>
                    <th class="p-3 font-semibold text-gray-600 text-sm text-left">Nama Produk</th>
                    <th class="p-3 font-semibold text-gray-600 text-sm text-left">Kategori</th>
                    <th class="p-3 font-semibold text-gray-600 text-sm text-left">Stok</th>
                    <th class="p-3 font-semibold text-gray-600 text-sm text-left">Harga</th>
                    <th class="p-3 font-semibold text-gray-600 text-sm text-left">Foto</th>
                    <th class="p-3 font-semibold text-gray-600 text-sm text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($products as $p)
                    <tr class="hover:bg-green-50 transition">
                        <td class="p-3 text-left align-middle">{{ $p->kode_produk }}</td>
                        <td class="p-3 text-left align-middle">{{ $p->nama_produk }}</td>
                        <td class="p-3 text-left align-middle">{{ ucfirst($p->kategori) }}</td>
                        <td class="p-3 text-left align-middle">{{ ucfirst($p->stock) }}</td>
                        <td class="p-3 text-left align-middle">Rp{{ number_format($p->harga, 0, ',', '.') }}</td>
                        <td class="p-3 text-left align-middle">
                            @if($p->foto && count($p->foto) > 0)
                                <img src="{{ asset($p->foto[0]) }}" alt="{{ $p->nama_produk }}"
                                     class="w-14 h-14 object-cover rounded shadow border border-gray-200" />
                            @else
                                <span class="text-gray-400 italic">Tidak ada foto</span>
                            @endif
                        </td>
                        <td class="p-3 text-left align-middle">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.produk.show', $p->id) }}"
                                    class="inline-flex items-center justify-center bg-blue-50 hover:bg-blue-100 text-blue-700 p-2 rounded transition"
                                    title="Detail">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <a href="{{ route('admin.produk.edit', $p->id) }}"
                                    class="inline-flex items-center justify-center bg-yellow-50 hover:bg-yellow-100 text-yellow-600 p-2 rounded transition"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.produk.destroy', $p->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus produk ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center bg-red-50 hover:bg-red-100 text-red-600 p-2 rounded transition"
                                        title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-4 text-center text-gray-400 italic">Belum ada produk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $products->withQueryString()->links() }}
    </div>
</div>
@endsection
