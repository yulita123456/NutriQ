<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Admin Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
    <style>
        .header-animate {
            transition: box-shadow 0.18s cubic-bezier(0.4,0,0.2,1), background 0.15s;
        }
        [x-cloak] { display: none !important; }
    </style>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gray-100 font-sans min-h-screen">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="fixed top-0 left-0 w-60 h-full bg-white border-r border-gray-200 shadow-sm flex flex-col justify-between z-30">
        <div>
            <!-- Logo & Brand -->
            <div class="flex items-center h-20 pl-6 mb-6 border-b border-gray-100 bg-green-100">
                <span class="inline-flex items-center justify-center w-16 h-16 bg-green-500 rounded-full text-white font-extrabold text-2xl">NQ</span>
                <span class="ml-4 text-2xl font-bold text-gray-800 tracking-tight">NutriQ</span>
            </div>
            <!-- Navigation -->
            <nav class="mt-2">
                <ul class="flex flex-col gap-1">
                    <li>
                        <a href="{{ route('admin.dashboard') }}"
                           class="flex items-center gap-3 px-6 py-3 text-gray-700 rounded-lg hover:bg-green-50 transition
                            {{ request()->routeIs('admin.dashboard') ? 'bg-green-100 text-green-700 font-semibold' : '' }}">
                            <i class="fas fa-tachometer-alt w-5"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.produk.index') }}"
                        class="flex items-center gap-3 px-6 py-3 text-gray-700 rounded-lg hover:bg-green-50 transition
                        {{ request()->routeIs('admin.produk.*') ? 'bg-green-100 text-green-700 font-semibold' : '' }}">
                            <i class="fas fa-box-open w-5"></i> Produk
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.pembayaran.index') }}"
                           class="flex items-center gap-3 px-6 py-3 text-gray-700 rounded-lg hover:bg-green-50 transition
                            {{ request()->routeIs('admin.pembayaran.*') ? 'bg-green-100 text-green-700 font-semibold' : '' }}">
                            <i class="fas fa-money-check-alt w-5"></i> Pembayaran
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.user.index') }}"
                           class="flex items-center gap-3 px-6 py-3 text-gray-700 rounded-lg hover:bg-green-50 transition
                           {{ request()->routeIs('admin.user.*') ? 'bg-green-100 font-bold' : '' }}">
                            <i class="fas fa-users w-5"></i> User
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.log_aktivitas') }}"
                           class="flex items-center gap-3 px-6 py-3 text-gray-700 rounded-lg hover:bg-green-50 transition
                           {{ request()->routeIs('admin.log_aktivitas*') ? 'bg-green-100 text-green-700 font-semibold' : '' }}">
                            <i class="fas fa-clipboard-list w-5"></i> Log Aktivitas
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.pengaturan.index') }}"
                           class="flex items-center gap-3 px-6 py-3 text-gray-700 rounded-lg hover:bg-green-50 transition
                            {{ request()->routeIs('admin.pengaturan.*') ? 'bg-green-100 text-green-700 font-semibold' : '' }}">
                            <i class="fas fa-cog w-5"></i> Pengaturan
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="flex-1 min-h-screen ml-60 flex flex-col">
        <!-- HEADER -->
        <header id="adminHeader"
            class="h-18 border-gray-100 bg-green-100 flex items-center justify-between px-14 sticky top-0 z-20 header-animate shadow transition-all duration-300"
            style="height: 79px;">

            <!-- KIRI: Judul -->
            <div class="flex items-center gap-6">
                <div class="flex flex-col">
                    @yield('header')
                </div>
            </div>

            <!-- KANAN: Notif & Avatar -->
            <div class="flex items-center gap-6">
                <!-- Notifikasi Bell -->
                <div x-data="{ showNotif: false }" class="relative">
                    <button class="relative p-2 text-gray-500 hover:text-green-600 focus:outline-none"
                            @click="showNotif = !showNotif">
                        <i class="fas fa-bell text-2xl"></i>
                        @if(!empty($notifikasi))
                            <span class="absolute -top-1 -right-1 inline-block w-3 h-3 bg-red-600 rounded-full border-2 border-white animate-pulse"></span>
                        @endif
                    </button>
                    <!-- Popup notifikasi -->
                    <div x-show="showNotif" x-transition @click.away="showNotif = false"
                        class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg py-2 z-50 border border-gray-100"
                        x-cloak>
                        <div class="font-bold px-4 pt-2 pb-1 text-gray-700 text-base">Notifikasi</div>
                        <div class="max-h-64 overflow-y-auto">
                            @forelse($notifikasi as $n)
                                <div class="px-4 py-3 border-b border-gray-50 text-gray-700 text-sm leading-relaxed">
                                    {!! $n['pesan'] !!}
                                </div>
                            @empty
                                <div class="px-4 py-4 text-gray-400 text-center text-sm italic">Tidak ada notifikasi.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Avatar Dropdown -->
                <div x-data="{ open: false, confirmLogout: false }" x-init="confirmLogout = false" class="relative">
                    <img
                        @click="open = !open"
                        src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Admin') }}&background=10B981&color=fff"
                        class="w-11 h-11 rounded-full border border-green-200 cursor-pointer"
                        alt="User" />

                    <!-- Dropdown menu -->
                    <div
                        x-show="open"
                        @click.away="open = false"
                        x-transition
                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg py-2 z-40 border border-gray-100"
                    >
                        <div class="px-4 py-2">
                            <div class="font-semibold text-gray-800 text-base">
                                {{ Auth::user()->name ?? 'Admin' }}
                            </div>
                            <div class="text-xs text-gray-500 break-all">
                                {{ Auth::user()->email ?? '-' }}
                            </div>
                        </div>
                        <div class="my-1 border-t border-gray-100"></div>
                        <a href="{{ route('admin.pengaturan.edit') }}"
                        class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-green-50 transition text-sm">
                            <i class="fas fa-user-edit"></i> Edit Profil & Password
                        </a>
                        <div class="my-1 border-t border-gray-100"></div>
                        <button
                            class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 transition text-sm"
                            @click="confirmLogout = true; open = false">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </div>
                    <!-- Modal Konfirmasi Logout -->
                    <div
                        x-show="confirmLogout"
                        x-transition
                        class="fixed inset-0 flex items-center justify-center z-50 bg-black/30"
                        x-cloak
                    >
                        <div class="bg-white rounded-xl shadow-lg p-6 max-w-xs text-center">
                            <div class="mb-2 text-xl font-bold text-gray-800">
                                Konfirmasi Logout
                            </div>
                            <div class="mb-4 text-gray-600">
                                Apakah Anda yakin ingin logout?
                            </div>
                            <div class="flex justify-center gap-4">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                                        Iya, Logout
                                    </button>
                                </form>
                                <button
                                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition"
                                    @click="confirmLogout = false"
                                >
                                    Batal
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <main class="flex-1 bg-gray-50 pt-10 px-10 pb-10">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.getElementById('adminHeader');
        // scroll listener ke window (bukan main)
        window.addEventListener('scroll', function() {
            const scrollTop = window.scrollY;
            if (scrollTop > 10) {
                // Saat scroll ke bawah: transparan + blur
                header.classList.remove('bg-white', 'shadow');
                header.classList.add('bg-white/30', 'backdrop-blur-md', 'border', 'border-green-50');
            } else {
                // Di atas: solid putih
                header.classList.remove('bg-white/30', 'backdrop-blur-md', 'border', 'border-green-50');
                header.classList.add('bg-green-100', 'shadow');
            }
        });
    });
</script>
</body>
</html>
