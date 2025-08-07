@extends('layouts.admin')
@section('header')
    <h1 class="text-3xl font-bold text-gray-800 mb-0">Detail User</h1>
@endsection
@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto">
        <!-- Card Utama -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                <div>
                    <div class="text-gray-500">Informasi lengkap dan riwayat transaksi.</div>
                </div>
                <a href="{{ route('admin.user.index') }}"
                   class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-5 py-2 rounded-lg shadow transition">
                   <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-5">
                    <div>
                        <div class="text-gray-500 text-sm mb-1">Nama Lengkap</div>
                        <div class="text-lg font-bold text-gray-800">{{ $user->name }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 text-sm mb-1">Email</div>
                        <div class="text-base text-gray-700">{{ $user->email }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 text-sm mb-1">No HP</div>
                        <div class="text-base text-gray-700">{{ $user->no_telp ?? '-' }}</div>
                    </div>
                </div>
                <div class="space-y-5">
                    <div>
                        <div class="text-gray-500 text-sm mb-1">Username</div>
                        <div class="text-base text-gray-700">{{ $user->username ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 text-sm mb-1">Role</div>
                        <span class="inline-block px-3 py-1 rounded-xl text-xs font-bold
                            {{ $user->role == 'admin' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($user->role ?? '-') }}
                        </span>
                    </div>
                    <div>
                        <div class="text-gray-500 text-sm mb-1">Tanggal Daftar</div>
                        <div class="text-base text-gray-700">{{ $user->created_at ? $user->created_at->format('d M Y H:i') : '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="text-right pt-8">
                <a href="{{ route('admin.user.edit', $user->id) }}"
                   class="inline-flex items-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-white font-semibold px-5 py-2 rounded-lg shadow transition">
                    <i class="fas fa-edit"></i> Edit User
                </a>
            </div>
        </div>

        <!-- Card Riwayat Transaksi -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
            <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fas fa-history"></i> Riwayat Transaksi
            </h3>
            @php
                $transactions = $user->transactions()->orderBy('created_at', 'desc')->take(10)->get();
            @endphp

            @if($transactions->count())
                <div class="overflow-x-auto rounded-xl">
                    <table class="min-w-full bg-gray-50 rounded-xl">
                        <thead class="bg-green-100 text-gray-700">
                            <tr>
                                <th class="p-3 font-semibold text-sm">Order ID</th>
                                <th class="p-3 font-semibold text-sm">Tanggal</th>
                                <th class="p-3 font-semibold text-sm">Total</th>
                                <th class="p-3 font-semibold text-sm">Status</th>
                                <th class="p-3 font-semibold text-sm">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($transactions as $trx)
                            <tr class="border-t border-gray-200 hover:bg-green-50 transition">
                                <td class="p-3 align-middle">{{ $trx->order_id }}</td>
                                <td class="p-3 align-middle">{{ $trx->created_at ? $trx->created_at->format('d M Y H:i') : '-' }}</td>
                                <td class="p-3 align-middle">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                                <td class="p-3 align-middle">
                                    <span class="inline-block px-3 py-1 rounded-xl text-xs font-bold
                                    @if($trx->status === 'settlement' || $trx->status === 'success' || $trx->status === 'selesai')
                                        bg-green-200 text-green-800
                                    @elseif($trx->status === 'pending' || $trx->status === 'proses')
                                        bg-yellow-100 text-yellow-700
                                    @elseif($trx->status === 'failed' || $trx->status === 'expire' || $trx->status === 'cancel')
                                        bg-red-100 text-red-700
                                    @else
                                        bg-gray-100 text-gray-600
                                    @endif
                                    ">
                                        {{ ucfirst($trx->status) }}
                                    </span>
                                </td>
                                <td class="p-3 align-middle">
                                    @if(Route::has('produk.show'))
                                        <a href="{{ route('produk.show', $trx->details->first()->product_id ?? '') }}"
                                           class="inline-flex items-center justify-center bg-blue-50 hover:bg-blue-100 text-blue-700 p-2 rounded transition"
                                           title="Lihat Produk">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-xs text-gray-500 mt-3">* Hanya 10 transaksi terbaru yang ditampilkan</div>
            @else
                <div class="text-center text-gray-400 italic py-8">User belum pernah melakukan transaksi.</div>
            @endif
        </div>
    </div>
</div>
@endsection
