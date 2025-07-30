@extends('layouts.admin')

@section('header')
    <h1 class="text-3xl font-bold text-gray-800 mb-0">Pengaturan Admin</h1>
@endsection

@section('content')
<div class="py-6">
    <!-- Info Halaman -->
    <div class="mb-6">
        <p class="text-gray-500">Kelola pengaturan sistem, profil, dan fitur admin NutriQ.</p>
    </div>

    <!-- Section: Akun & Profil -->
    <div class="bg-white rounded-xl shadow border border-gray-100 mb-6 p-6">
        <div class="flex items-center gap-2 mb-4">
            <i class="fas fa-user-cog text-green-500"></i>
            <h2 class="text-lg font-semibold text-gray-800">Akun & Profil</h2>
        </div>
        <div class="flex flex-col md:flex-row md:items-center md:gap-8 gap-3">
            <div class="flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Admin') }}&background=10B981&color=fff"
                    class="w-14 h-14 rounded-full border border-green-200" alt="User" />
                <div>
                    <div class="font-semibold text-gray-700">{{ Auth::user()->name ?? '-' }}</div>
                    <div class="text-xs text-gray-500">{{ Auth::user()->email ?? '-' }}</div>
                </div>
            </div>
            <div class="flex flex-col gap-2 md:ml-auto mt-4 md:mt-0">
                <a href="{{ route('admin.pengaturan.edit') }}"
                    class="inline-flex items-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold px-4 py-2 rounded-lg shadow transition text-sm">
                    <i class="fas fa-user-edit"></i> Edit Profil & Password
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                <button type="button"
                    onclick="showLogoutConfirm()"
                    class="inline-flex items-center gap-2 bg-red-50 hover:bg-red-100 text-red-600 font-semibold px-4 py-2 rounded-lg shadow transition text-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout Semua Perangkat
                </button>
            </div>
        </div>
    </div>

    <!-- Section: Umum -->
    <div class="bg-white rounded-xl shadow border border-gray-100 mb-6 p-6">
        <div class="flex items-center gap-2 mb-4">
            <i class="fas fa-cog text-green-500"></i>
            <h2 class="text-lg font-semibold text-gray-800">Umum</h2>
        </div>
        <div class="flex flex-col md:flex-row gap-4">
            <a href="#" class="inline-flex items-center gap-2 bg-green-50 hover:bg-green-100 text-green-700 font-semibold px-4 py-2 rounded-lg transition text-sm">
                <i class="fas fa-image"></i> Ganti Logo/Favicon
            </a>
            <a href="#" class="inline-flex items-center gap-2 bg-green-50 hover:bg-green-100 text-green-700 font-semibold px-4 py-2 rounded-lg transition text-sm">
                <i class="fas fa-database"></i> Backup Database
            </a>
        </div>
    </div>

    <!-- Section: Lain-lain -->
    <div class="bg-white rounded-xl shadow border border-gray-100 p-6">
        <div class="flex items-center gap-2 mb-4">
            <i class="fas fa-info-circle text-green-500"></i>
            <h2 class="text-lg font-semibold text-gray-800">Lain-lain</h2>
        </div>
        <div class="flex flex-col md:flex-row gap-4">
            <span class="inline-flex items-center gap-2 text-gray-600 text-sm">
                <i class="fas fa-code-branch"></i>
                <span>Versi aplikasi: <span class="font-semibold text-gray-800">1.0.0</span></span>
            </span>
            <a href="mailto:support@nutriq.com"
                class="inline-flex items-center gap-2 text-green-700 hover:text-green-900 hover:underline text-sm">
                <i class="fas fa-envelope"></i> Kontak Support
            </a>
        </div>
    </div>
</div>

<!-- Modal Logout Konfirmasi -->
<div id="logoutModal"
    class="hidden fixed inset-0 z-50 bg-black bg-opacity-30 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-xs relative">
        <h3 class="font-bold text-lg mb-4 text-gray-800 flex items-center gap-2"><i class="fas fa-sign-out-alt text-red-500"></i> Konfirmasi Logout</h3>
        <div class="text-gray-700 text-sm mb-6">Apakah Anda yakin ingin logout dari semua perangkat?</div>
        <div class="flex gap-3 justify-end">
            <button onclick="closeLogoutModal()" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-gray-800 font-semibold">Batal</button>
            <button onclick="document.getElementById('logout-form').submit();" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 font-semibold">Iya, Logout</button>
        </div>
        <button onclick="closeLogoutModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showLogoutConfirm() {
        document.getElementById('logoutModal').classList.remove('hidden');
    }
    function closeLogoutModal() {
        document.getElementById('logoutModal').classList.add('hidden');
    }
    // Optional: esc to close modal
    document.addEventListener('keydown', function(e) {
        if(e.key === "Escape") closeLogoutModal();
    });
</script>
@endpush
