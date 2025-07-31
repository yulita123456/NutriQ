@extends('layouts.guest')

@section('content')
<div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg border border-gray-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Login Admin NutriQ</h2>

    @if (session('status'))
        <div class="mb-4 text-green-600 font-semibold text-center">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-semibold mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 @error('email') border-red-500 @enderror" />
            @error('email')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4 relative"> {{-- Tambahkan 'relative' di sini untuk penempatan ikon --}}
            <label for="password" class="block text-gray-700 font-semibold mb-1">Password</label>
            <input id="password" type="password" name="password" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 @error('password') border-red-500 @enderror" />
            <span class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer top-7" id="togglePassword">
                {{-- Icon mata, gunakan Font Awesome. Pastikan Anda sudah menyertakan CDN Font Awesome di layout.guest atau file terkait. --}}
                <i class="fas fa-eye text-gray-500 hover:text-gray-700"></i>
            </span>
            @error('password')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center mb-6">
            <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 text-green-600 focus:ring-green-400 border-gray-300 rounded" />
            <label for="remember_me" class="ml-2 block text-gray-700 text-sm">Remember me</label>
        </div>

        <div class="flex items-center justify-between">
            @if (Route::has('admin.password.request'))
                <a href="{{ route('admin.password.request') }}"
                    class="text-sm text-green-700 hover:underline focus:outline-none focus:ring-2 focus:ring-green-400 rounded">
                    Lupa Password?
                </a>
            @endif

            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400">
                Log in
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            // Toggle the type attribute
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);

            // Toggle the eye icon
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });
</script>
@endpush
@endsection
