@extends('layouts.admin')
@section('header')
    <h1 class="text-3xl font-bold text-gray-800 mb-0">Edit User</h1>
@endsection
@section('content')
<div class="py-8">
    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
            <div class="flex items-center mb-8">
                <a href="{{ route('admin.user.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded shadow font-semibold transition mr-3">
                   <i class="fas fa-arrow-left mr-2"></i>
                   Kembali
                </a>
            </div>

            <form action="{{ route('admin.user.update', $user->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full px-4 py-2 border rounded shadow-sm focus:ring-green-200 focus:border-green-500"
                        required>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="w-full px-4 py-2 border rounded shadow-sm focus:ring-green-200 focus:border-green-500"
                        required>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">No HP</label>
                    <input type="text" name="no_telp" value="{{ old('no_telp', $user->no_telp) }}"
                        class="w-full px-4 py-2 border rounded shadow-sm focus:ring-green-200 focus:border-green-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Username</label>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}"
                        class="w-full px-4 py-2 border rounded shadow-sm focus:ring-green-200 focus:border-green-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Role</label>
                    <select name="role"
                        class="w-full px-4 py-2 border rounded shadow-sm focus:ring-green-200 focus:border-green-500"
                        required>
                        <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="pt-3 text-right">
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg shadow transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
