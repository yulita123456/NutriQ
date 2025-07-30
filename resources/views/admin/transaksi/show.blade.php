@extends('layouts.admin')
@section('header')
    <h1 class="text-3xl font-bold text-gray-800 mb-0">Detail Transaksi</h1>
@endsection
@section('content')
<div class="bg-white rounded-2xl shadow-lg px-8 py-8 max-w-2xl mx-auto">

    <div class="mb-6">
        <div class="flex items-center gap-4">
            <span class="text-lg font-semibold text-green-700">{{ $transaction->order_id }}</span>
            <span class="px-2 py-1 rounded
                @if ($transaction->status === 'settlement')
                    bg-green-200 text-green-800
                @elseif ($transaction->status === 'pending')
                    bg-yellow-200 text-yellow-900
                @else
                    bg-red-200 text-red-800
                @endif
                text-xs font-semibold"
            >
                {{ ucfirst($transaction->status) }}
            </span>
        </div>
        <div class="text-gray-500 text-sm mt-1">
            Tanggal: {{ $transaction->created_at->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="mb-6">
        <div><span class="font-semibold">User:</span> {{ $transaction->user->name ?? '-' }} ({{ $transaction->user->email ?? '-' }})</div>
        <div><span class="font-semibold">Total:</span> Rp {{ number_format($transaction->total, 0, ',', '.') }}</div>
        <div><span class="font-semibold">Snap Token:</span> <span class="font-mono text-xs">{{ $transaction->snap_token ?? '-' }}</span></div>
        <div><span class="font-semibold">Redirect URL:</span>
            @if($transaction->redirect_url)
                <a href="{{ $transaction->redirect_url }}" target="_blank" class="text-blue-600 underline text-xs">Lihat di Midtrans</a>
            @else
                <span class="text-gray-400 text-xs">-</span>
            @endif
        </div>
    </div>

    <h3 class="text-lg font-semibold mb-2">Daftar Produk</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full border rounded">
            <thead>
                <tr class="bg-gray-100 text-gray-800">
                    <th class="p-2 text-left text-sm">Nama Produk</th>
                    <th class="p-2 text-left text-sm">Qty</th>
                    <th class="p-2 text-left text-sm">Harga</th>
                    <th class="p-2 text-left text-sm">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->details as $item)
                <tr class="border-b last:border-0">
                    <td class="p-2">{{ $item->product->nama_produk ?? '-' }}</td>
                    <td class="p-2">{{ $item->qty }}</td>
                    <td class="p-2">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="p-2">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        <a href="{{ route('admin.pembayaran.index') }}" class="text-green-700 hover:underline">&larr; Kembali ke Log Pembayaran</a>
    </div>
</div>
@endsection
