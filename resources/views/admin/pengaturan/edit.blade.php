@extends('layouts.admin')

@section('content')
<div class="max-w-xl mx-auto bg-white rounded-2xl shadow-lg px-6 py-8">
    <div class="mb-8 flex items-center gap-3">
        <div class="bg-green-100 text-green-600 rounded-full p-3">
            <i class="fas fa-user-cog fa-lg"></i>
        </div>
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Edit Profil Admin</h1>
            <div class="text-gray-500 text-sm">Perbarui informasi profil dan password Anda.</div>
        </div>
    </div>

    {{-- Notifikasi sukses --}}
    @if(session('success'))
        <div class="mb-4 bg-green-50 text-green-800 rounded-lg p-4 border border-green-200">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Tampilkan error --}}
    @if($errors->any())
        <div class="mb-4 bg-red-50 text-red-800 rounded-lg p-4 border border-red-200">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li><i class="fas fa-exclamation-circle mr-2"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.pengaturan.update') }}" method="POST" class="space-y-6">
        @csrf
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-green-400 focus:border-green-400 transition">
        </div>
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-green-400 focus:border-green-400 transition">
        </div>
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Username</label>
            <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-green-400 focus:border-green-400 transition">
        </div>
        <div>
            <label class="block font-semibold text-gray-700 mb-1">No. Telepon</label>
            <input type="text" name="no_telp" value="{{ old('no_telp', $user->no_telp) }}"
                class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-green-400 focus:border-green-400 transition">
        </div>
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Alamat</label>
            <textarea name="alamat" rows="2"
                class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-green-400 focus:border-green-400 transition">{{ old('alamat', $user->alamat) }}</textarea>
        </div>
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Password Baru</label>
            <input type="password" name="password" autocomplete="new-password"
                class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-green-400 focus:border-green-400 transition" placeholder="Biarkan kosong jika tidak ingin mengubah">
        </div>
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Konfirmasi Password Baru</label>
            <input type="password" name="password_confirmation" autocomplete="new-password"
                class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-green-400 focus:border-green-400 transition" placeholder="Ulangi password baru">
        </div>
        <div class="text-right pt-2">
            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg shadow transition">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
