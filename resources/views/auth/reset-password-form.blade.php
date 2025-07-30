@extends('layouts.guest')

@section('content')
<div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg border border-gray-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Atur Password Baru</h2>

    <form method="POST" action="{{ route('admin.password.update') }}">
        @csrf

        {{-- Input tersembunyi untuk membawa data penting --}}
        <input type="hidden" name="email" value="{{ session('email') }}">
        <input type="hidden" name="code" value="{{ session('code') }}">

        {{-- Input Password Baru --}}
        <div class="mb-4">
            <label for="password" class="block text-gray-700 font-semibold mb-1">Password Baru</label>
            <input id="password" type="password" name="password" required autofocus
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 @error('password') border-red-500 @enderror" />
            @error('password')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Input Konfirmasi Password --}}
        <div class="mb-6">
            <label for="password_confirmation" class="block text-gray-700 font-semibold mb-1">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400" />
        </div>

        <div class="flex items-center justify-end">
            <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400">
                Simpan Password Baru
            </button>
        </div>
    </form>
</div>
@endsection
