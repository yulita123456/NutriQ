@extends('layouts.admin')
@section('header')
    <h1 class="text-3xl font-bold text-gray-800 mb-0">Daftar User</h1>
@endsection
@section('content')
<div class="py-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <div class="text-gray-500">Kelola seluruh user aplikasi NutriQ.</div>
        </div>
        {{-- Tambah User? (opsional) --}}
        {{-- <a href="{{ route('admin.user.create') }}"
           class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg shadow transition focus:outline-none">
            <i class="fas fa-plus"></i> Tambah User Baru
        </a> --}}
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-100 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 font-semibold text-gray-600 text-sm">Nama</th>
                    <th class="p-3 font-semibold text-gray-600 text-sm">Email</th>
                    <th class="p-3 font-semibold text-gray-600 text-sm">No HP</th>
                    <th class="p-3 font-semibold text-gray-600 text-sm">Role</th>
                    <th class="p-3 font-semibold text-center text-gray-600 text-sm">Tanggal Daftar</th>
                    <th class="p-3 font-semibold text-center text-gray-600 text-sm">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($users as $u)
                    <tr class="hover:bg-green-50 transition">
                        <td class="p-3 align-middle">{{ $u->name }}</td>
                        <td class="p-3 align-middle">{{ $u->email }}</td>
                        <td class="p-3 align-middle">{{ $u->no_telp ?? '-' }}</td>
                        <td class="p-3 align-middle">
                            <span class="inline-block px-3 py-1 rounded-lg text-xs font-bold
                                {{ $u->role == 'admin' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($u->role ?? '-') }}
                            </span>
                        </td>
                        <td class="p-3 align-middle text-center">
                            {{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}
                        </td>
                        <td class="p-3 align-middle">
                            <div class="flex gap-2 justify-center">
                                <a href="{{ route('admin.user.show', $u->id) }}"
                                    class="inline-flex items-center justify-center bg-blue-50 hover:bg-blue-100 text-blue-700 p-2 rounded transition"
                                    title="Detail">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <a href="{{ route('admin.user.edit', $u->id) }}"
                                    class="inline-flex items-center justify-center bg-yellow-50 hover:bg-yellow-100 text-yellow-600 p-2 rounded transition"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.user.destroy', $u->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus user ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center bg-red-50 hover:bg-red-100 text-red-600 p-2 rounded transition"
                                        title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-gray-400 italic">Belum ada user.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
