@extends('layouts.admin')
@section('header')
    <h1 class="text-3xl font-bold text-gray-800 mb-0">Log Pembayaran</h1>
@endsection
@section('content')
<div class="bg-white rounded-2xl shadow-lg px-6 py-6 md:px-10 md:py-8">
    <!-- Judul + Search -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <p class="text-gray-500 text-xs md:text-sm">Semua transaksi pembayaran aplikasi NutriQ dalam satu halaman.</p>
        </div>
        <form method="GET" action="{{ route('admin.pembayaran.index') }}" class="flex gap-2 w-full md:w-64">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari Order ID / Nama User"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-green-200" />
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-xs font-semibold">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <!-- Filter + Download -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-2 md:gap-6 mb-6">
        <!-- FILTER -->
        <form method="GET" action="{{ route('admin.pembayaran.index') }}"
            class="flex flex-wrap gap-2 items-end bg-green-50 px-4 py-3 rounded-xl w-full md:w-auto border border-green-100">
            <div>
                <label class="text-xs font-medium mr-1">Bulan</label>
                <select name="bulan" class="border border-gray-300 rounded px-2 py-1 text-xs">
                    <option value="">Semua</option>
                    @foreach(range(1, 12) as $b)
                        <option value="{{ $b }}" {{ request('bulan') == $b ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $b)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium mr-1">Tahun</label>
                <select name="tahun" class="border border-gray-300 rounded px-2 py-1 text-xs">
                    <option value="">Semua</option>
                    @foreach(range(now()->year, now()->year-4) as $y)
                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium mr-1">Status</label>
                <select name="status" class="border border-gray-300 rounded px-2 py-1 text-xs">
                    <option value="">Semua</option>
                    <option value="settlement" {{ request('status') == 'settlement' ? 'selected' : '' }}>Lunas</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Gagal</option>
                </select>
            </div>
            <button type="submit"
                class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg shadow font-semibold text-xs">
                Filter
            </button>
            <a href="{{ route('admin.pembayaran.index') }}"
                class="inline-block bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold text-xs hover:bg-gray-400">
                Reset
            </a>
        </form>
        <!-- Tombol Download -->
        <div class="flex flex-row gap-2 md:gap-3 mt-3 md:mt-0">
            <form method="GET" action="{{ route('admin.pembayaran.export') }}">
                <input type="hidden" name="bulan" value="{{ request('bulan') }}">
                <input type="hidden" name="tahun" value="{{ request('tahun') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <button type="submit"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg shadow hover:bg-green-700 text-xs font-semibold flex items-center gap-2">
                    <i class="fas fa-file-excel"></i>
                    <span class="hidden sm:inline">Download Excel</span>
                </button>
            </form>
            <form method="GET" action="{{ route('admin.pembayaran.rekap') }}">
                <input type="hidden" name="bulan" value="{{ request('bulan') }}">
                <input type="hidden" name="tahun" value="{{ request('tahun') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <button type="submit"
                    class="bg-green-700 text-white px-4 py-2 rounded-lg shadow hover:bg-green-800 text-xs font-semibold flex items-center gap-2">
                    <i class="fas fa-table"></i>
                    <span class="hidden sm:inline">Download Rekap</span>
                </button>
            </form>
        </div>
    </div>

    <!-- OPSIONAL: Summary ringkas -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4 text-xs">
        <div class="bg-green-100 rounded-xl py-3 text-center">
            <div class="font-semibold text-green-900">{{ $transactions->total() }}</div>
            <div class="text-green-500">Total Transaksi</div>
        </div>
        <div class="bg-green-50 rounded-xl py-3 text-center">
            <div class="font-semibold text-green-800">
                Rp {{ number_format($transactions->sum('total'), 0, ',', '.') }}
            </div>
            <div class="text-green-500">Total Pemasukan</div>
        </div>
        <div class="bg-green-50 rounded-xl py-3 text-center">
            <div class="font-semibold text-green-800">{{ $transactions->where('status','settlement')->count() }}</div>
            <div class="text-green-500">Lunas</div>
        </div>
        <div class="bg-green-50 rounded-xl py-3 text-center">
            <div class="font-semibold text-green-800">{{ $transactions->where('status','pending')->count() }}</div>
            <div class="text-green-500">Menunggu</div>
        </div>
    </div>

    <!-- TABLE TRANSAKSI -->
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto border-collapse rounded-xl overflow-hidden shadow">
            <thead>
                <tr class="bg-green-100 text-green-900 text-left">
                    <th class="p-3 font-semibold">Order ID</th>
                    <th class="p-3 font-semibold">User</th>
                    <th class="p-3 font-semibold">Total</th>
                    <th class="p-3 font-semibold">Status</th>
                    <th class="p-3 font-semibold">Tanggal</th>
                    <th class="p-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $trx)
                <tr class="border-b last:border-0 hover:bg-green-50 transition">
                    <td class="p-3 font-mono text-xs">{{ $trx->order_id }}</td>
                    <td class="p-3">{{ $trx->user->name ?? '-' }}</td>
                    <td class="p-3">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                    <td class="p-3">
                        @if ($trx->status == 'settlement')
                            <span class="inline-block px-2 py-1 rounded-full bg-green-200 text-green-800 text-xs font-semibold">Lunas</span>
                        @elseif ($trx->status == 'pending')
                            <span class="inline-block px-2 py-1 rounded-full bg-yellow-200 text-yellow-900 text-xs font-semibold">Menunggu</span>
                        @else
                            <span class="inline-block px-2 py-1 rounded-full bg-red-200 text-red-800 text-xs font-semibold">{{ ucfirst($trx->status) }}</span>
                        @endif
                    </td>
                    <td class="p-3 text-xs">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                    <td class="p-3">
                        <a href="{{ route('admin.pembayaran.show', $trx->id) }}"
                           class="inline-flex items-center gap-1 text-blue-700 hover:underline text-xs font-semibold">
                           <i class="fas fa-eye"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-gray-400 py-8">Belum ada data transaksi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <div class="mt-6 flex justify-end">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
