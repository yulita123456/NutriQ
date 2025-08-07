@extends('layouts.guest')

@section('content')
<div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg border border-gray-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Atur Password Baru</h2>

    <form method="POST" action="{{ route('admin.password.update') }}">
        @csrf

        {{-- Input tersembunyi untuk membawa data penting --}}
        <input type="hidden" name="email" value="{{ session('email') }}">
        <input type="hidden" name="code" value="{{ session('code') }}">

        {{-- Tampilkan Error Validasi Umum Jika Ada --}}
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

        {{-- ==================================================================== --}}
        {{-- PERUBAHAN DIMULAI DI SINI --}}
        {{-- ==================================================================== --}}

        {{-- Input Password Baru --}}
        <div class="mb-4 relative">
            <label for="password" class="block text-gray-700 font-semibold mb-1">Password Baru</label>
            <input id="password" type="password" name="password" required autofocus
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 @error('password') border-red-500 @enderror" />
            {{-- Ikon Mata Ditambahkan di Sini --}}
            <span class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer top-7" onclick="togglePasswordVisibility('password')">
                <i class="fas fa-eye text-gray-500 hover:text-gray-700"></i>
            </span>
            @error('password')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Input Konfirmasi Password --}}
        <div class="mb-6 relative">
            <label for="password_confirmation" class="block text-gray-700 font-semibold mb-1">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400" />
            {{-- Ikon Mata Ditambahkan di Sini --}}
            <span class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer top-7" onclick="togglePasswordVisibility('password_confirmation')">
                <i class="fas fa-eye text-gray-500 hover:text-gray-700"></i>
            </span>
        </div>

        {{-- ==================================================================== --}}
        {{-- AKHIR DARI PERUBAHAN --}}
        {{-- ==================================================================== --}}


        <div class="flex items-center justify-end">
            <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400">
                Simpan Password Baru
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function togglePasswordVisibility(fieldId) {
        const passwordField = document.getElementById(fieldId);
        // Dapatkan elemen span yang diklik, lalu cari ikon di dalamnya
        const icon = event.currentTarget.querySelector('i');

        // Ganti tipe input dari 'password' ke 'text' atau sebaliknya
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);

        // Ganti ikon mata
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    }
</script>
@endpush
@endsection
