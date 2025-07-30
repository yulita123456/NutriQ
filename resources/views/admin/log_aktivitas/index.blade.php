@extends('layouts.admin')
@section('header')
    <h1 class="text-3xl font-bold text-gray-800 mb-0">Log Aktivitas</h1>
@endsection
@section('content')
<div class="py-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-7">
        <div>
            <p class="text-gray-500">Rekam jejak seluruh aksi user dan admin di aplikasi NutriQ.</p>
        </div>
    </div>

    {{-- Filter/Search Form --}}
    <form method="GET" class="flex flex-col md:flex-row md:items-end gap-4 mb-5">
        {{-- Search Aksi/Deskripsi --}}
        <div>
            <label class="block text-sm text-gray-600 mb-1" for="search">Cari Aksi/Deskripsi</label>
            <input type="text" id="search" name="search" value="{{ request('search') }}"
                placeholder="Cari log..."
                class="border border-gray-300 rounded px-3 py-2 text-sm w-full md:w-56 focus:ring focus:ring-green-100">
        </div>

        {{-- Filter Role --}}
        <div>
            <label class="block text-sm text-gray-600 mb-1" for="role">Role</label>
            <select id="role" name="role" class="border border-gray-300 rounded px-3 py-2 text-sm w-full md:w-32">
                <option value="" {{ request('role') == '' ? 'selected' : '' }}>Semua</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
            </select>
        </div>

        {{-- Filter Kategori --}}
        <div>
            <label class="block text-sm text-gray-600 mb-1" for="kategori">Kategori</label>
            <select id="kategori" name="kategori" class="border border-gray-300 rounded px-3 py-2 text-sm w-full md:w-40">
                <option value="" {{ request('kategori') == '' ? 'selected' : '' }}>Semua</option>
                <option value="produk" {{ request('kategori') == 'produk' ? 'selected' : '' }}>Produk</option>
                <option value="user" {{ request('kategori') == 'user' ? 'selected' : '' }}>User</option>
                <option value="transaksi" {{ request('kategori') == 'transaksi' ? 'selected' : '' }}>Transaksi</option>
                <option value="profile" {{ request('kategori') == 'profile' ? 'selected' : '' }}>Profile</option>
                <option value="scan" {{ request('kategori') == 'scan' ? 'selected' : '' }}>Scan</option>
                <option value="login" {{ request('kategori') == 'login' ? 'selected' : '' }}>Login</option>
            </select>
        </div>

        {{-- Filter Tanggal Mulai --}}
        <div>
            <label class="block text-sm text-gray-600 mb-1" for="from">Tanggal Mulai</label>
            <input type="date" id="from" name="from" value="{{ request('from') }}"
                class="border border-gray-300 rounded px-3 py-2 text-sm w-full md:w-36">
        </div>

        {{-- Filter Tanggal Akhir --}}
        <div>
            <label class="block text-sm text-gray-600 mb-1" for="to">Tanggal Akhir</label>
            <input type="date" id="to" name="to" value="{{ request('to') }}"
                class="border border-gray-300 rounded px-3 py-2 text-sm w-full md:w-36">
        </div>

        {{-- Tombol Filter --}}
        <div>
            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg shadow">
                Filter
            </button>
        </div>

        {{-- Reset Filter --}}
        <div>
            <a href="{{ route('admin.log_aktivitas') }}"
                class="text-sm text-gray-600 underline hover:text-green-600 transition">
                Reset
            </a>
        </div>
    </form>

    {{-- Tabel Log Aktivitas --}}
    <div class="bg-white rounded-xl shadow border border-gray-100 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 font-semibold text-gray-600 text-sm">Waktu</th>
                    <th class="p-3 font-semibold text-gray-600 text-sm">User/Admin</th>
                    <th class="p-3 font-semibold text-gray-600 text-sm">Role</th>
                    <th class="p-3 font-semibold text-gray-600 text-sm">Aksi</th>
                    <th class="p-3 font-semibold text-gray-600 text-sm">Deskripsi</th>
                    <th class="p-3 font-semibold text-gray-600 text-sm text-center">Detail</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($logs as $log)
                <tr class="hover:bg-green-50 transition">
                    <td class="p-3 align-middle whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                    </td>
                    <td class="p-3 align-middle">
                        {{ $log->user->name ?? '-' }}
                        <div class="text-xs text-gray-400">{{ $log->user->email ?? '-' }}</div>
                    </td>
                    <td class="p-3 align-middle whitespace-nowrap">
                        <span
                            class="px-2 py-1 rounded-lg text-xs font-bold
                            {{ $log->role === 'admin' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-green-600' }}">
                            {{ ucfirst($log->role) }}
                        </span>
                    </td>
                    <td class="p-3 align-middle font-semibold whitespace-nowrap">{{ $log->aksi }}</td>
                    <td class="p-3 align-middle max-w-xs truncate" title="{{ $log->deskripsi }}">
                        {{ \Illuminate\Support\Str::limit($log->deskripsi, 45) }}
                    </td>
                    <td class="p-3 align-middle text-center whitespace-nowrap">
                        <button
                            class="inline-flex items-center gap-1 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded px-3 py-1 text-xs font-semibold"
                            onclick="showLogDetail({{ $log->id }})"
                            aria-label="Detail log aktivitas">
                            <i class="fas fa-info-circle"></i> Detail
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-4 text-center text-gray-400 italic">Belum ada aktivitas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $logs->withQueryString()->links() }}
    </div>
</div>

{{-- Modal Detail Log --}}
<div id="logModal"
    class="hidden fixed inset-0 z-50 bg-black bg-opacity-30 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-xl relative">
        <button onclick="closeLogModal()"
            class="absolute top-3 right-3 text-gray-400 hover:text-gray-800" aria-label="Close modal">
            <i class="fas fa-times"></i>
        </button>
        <div id="logModalContent" class="max-h-[400px] overflow-y-auto">
            <!-- AJAX content will be loaded here -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // AJAX show detail log modal
    function showLogDetail(id) {
        fetch('/admin/log-aktivitas/' + id)
            .then(res => res.json())
            .then(data => {
                let html = `
                    <h3 class="font-bold text-lg mb-2">Detail Log Aktivitas</h3>
                    <ul class="space-y-1 text-gray-700 text-sm">
                        <li><span class="font-semibold">Waktu:</span> ${data.created_at}</li>
                        <li><span class="font-semibold">User/Admin:</span> ${data.user ? data.user.name : '-'}</li>
                        <li><span class="font-semibold">Email:</span> ${data.user ? data.user.email : '-'}</li>
                        <li><span class="font-semibold">Role:</span> ${data.role}</li>
                        <li><span class="font-semibold">Aksi:</span> ${data.aksi}</li>
                        <li><span class="font-semibold">Deskripsi:</span> ${data.deskripsi || '-'}</li>
                        <li><span class="font-semibold">IP Address:</span> ${data.ip_address || '-'}</li>
                    </ul>
                `;
                document.getElementById('logModalContent').innerHTML = html;
                document.getElementById('logModal').classList.remove('hidden');
            })
            .catch(() => {
                alert('Gagal memuat detail log aktivitas.');
            });
    }
    function closeLogModal() {
        document.getElementById('logModal').classList.add('hidden');
    }
    // Optional: esc to close modal
    document.addEventListener('keydown', function(e) {
        if(e.key === "Escape") closeLogModal();
    });
</script>
@endpush

