@extends('layouts.admin')

@section('header')
    <h1 class="text-3xl font-bold text-gray-800 mb-0">Dashboard Admin</h1>
@endsection

@section('content')
<div class="space-y-8">

    <!-- Judul -->
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
        <div>
            <p class="text-gray-500">Pantau performa dan statistik sistem aplikasi NutriQ.</p>
        </div>
    </div>

<!-- Statistik Ringkas -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-lg p-6 shadow text-center border border-gray-100">
        <div class="text-gray-400 mb-1"><i class="fas fa-box-open fa-lg"></i></div>
        <div class="text-3xl font-bold text-green-700">{{ $productCount }}</div>
        <div class="text-gray-500">Produk</div>
    </div>
    <div class="bg-white rounded-lg p-6 shadow text-center border border-gray-100">
        <div class="text-gray-400 mb-1"><i class="fas fa-users fa-lg"></i></div>
        <div class="text-3xl font-bold text-green-700">{{ $userCount }}</div>
        <div class="text-gray-500">User</div>
    </div>
    <div class="bg-white rounded-lg p-6 shadow text-center border border-gray-100">
        <div class="text-gray-400 mb-1"><i class="fas fa-money-bill-wave fa-lg"></i></div>
        <div class="text-3xl font-bold text-green-700">
            {{ number_format($totalIncome ?? 0, 0, ',', '.') }}
        </div>
        <div class="text-gray-500">Total Pemasukan</div>
    </div>
</div>

    <!-- Chart Penjualan -->
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-700">Grafik Penjualan</h2>
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex gap-2 items-center">
            <label class="text-sm text-gray-600">Tahun:</label>
            <select name="tahun" class="border border-gray-300 rounded px-2 py-1 text-sm">
                @foreach(range(now()->year, now()->year-4) as $y)
                    <option value="{{ $y }}" {{ request('tahun', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <label class="text-sm text-gray-600">Bulan:</label>
            <select name="bulan" class="border border-gray-300 rounded px-2 py-1 text-sm">
                <option value="">Semua</option>
                @foreach(range(1, 12) as $b)
                    <option value="{{ $b }}" {{ request('bulan') == $b ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $b)->format('F') }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-semibold">
                Tampilkan
            </button>
        </form>
        <div class="flex gap-1">
            <!-- Tombol Download Excel -->
            <form method="GET" action="{{ route('admin.dashboard.export') }}">
                <input type="hidden" name="tahun" value="{{ request('tahun', now()->year) }}">
                <input type="hidden" name="bulan" value="{{ request('bulan') }}">
                <button type="submit"
                    class="bg-green-600 text-white px-3 py-1 rounded-l text-xs font-semibold flex items-center gap-2 hover:bg-green-700 border-r border-green-700">
                    <i class="fas fa-file-excel"></i> Download Excel
                </button>
            </form>
            <!-- Tombol Download Rekap Total -->
            <form method="GET" action="{{ route('admin.dashboard.rekap') }}">
                <input type="hidden" name="tahun" value="{{ request('tahun', now()->year) }}">
                <input type="hidden" name="bulan" value="{{ request('bulan') }}">
                <button type="submit"
                    class="bg-green-700 text-white px-3 py-1 rounded-r text-xs font-semibold flex items-center gap-2 hover:bg-green-800">
                    <i class="fas fa-table"></i> Download Rekap Total
                </button>
            </form>
        </div>
    </div>
        <canvas id="salesChart" height="120"></canvas>
    </div>

<!-- Aktivitas Terbaru -->
<div class="bg-white rounded-lg shadow p-6 border border-gray-100">
    <h2 class="text-lg font-semibold text-gray-700 mb-4 flex justify-between items-center">
        <span>Aktivitas Terbaru</span>
        <a href="{{ route('admin.log_aktivitas') }}" class="text-green-600 hover:underline text-sm font-semibold">
            Lihat Semua
        </a>
    </h2>
    <ul class="space-y-2 text-gray-600 text-sm">
        @forelse ($latestLogs as $log)
            @php
                $icon = match($log->role) {
                    'admin' => '🟢',
                    'user' => '🟠',
                    default => '⚪',
                };
            @endphp
            <li>
                {!! $icon !!}
                <strong>{{ $log->aksi }}</strong>
                @if (strlen($log->deskripsi) > 40)
                    {{ substr($log->deskripsi, 0, 40) . '...' }}
                @else
                    {{ $log->deskripsi }}
                @endif
                pada {{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }}
            </li>
        @empty
            <li class="italic text-gray-400">Belum ada aktivitas terbaru.</li>
        @endforelse
    </ul>
</div>

</div>
@endsection

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($salesLabels) !!},
                datasets: [{
                    label: 'Pemasukan (Rp)',
                    data: {!! json_encode($salesValues) !!},
                    backgroundColor: 'rgba(16, 185, 129, 0.3)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    maxBarThickness: 28
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false }},
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { weight: 'bold' } }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: { color: '#64748b', callback: function(value) { return 'Rp' + value.toLocaleString(); } }
                    }
                }
            }
        });
    });
    </script>
@endpush
