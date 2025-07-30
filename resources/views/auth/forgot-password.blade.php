@extends('layouts.guest')

@section('content')
<div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg border border-gray-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-2 text-center">Lupa Password Admin</h2>
    <p class="text-center text-gray-600 mb-6">
        Masukkan email Anda, kirim kode, lalu verifikasi untuk melanjutkan.
    </p>

    {{-- Notifikasi untuk pesan sukses/error dari JavaScript --}}
    <div id="notification" class="hidden mb-4 text-center font-semibold p-3 rounded-lg"></div>

    <form method="POST" action="{{ route('admin.password.verify.code') }}">
        @csrf

        {{-- Input Email dan Tombol Kirim Kode --}}
        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-semibold mb-1">Alamat Email</label>
            <div class="flex items-center space-x-2">
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="flex-grow w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 @error('email') border-red-500 @enderror" />

                {{-- WARNA TOMBOL DIUBAH MENJADI HIJAU --}}
                <button type="button" id="send-code-btn"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 whitespace-nowrap flex justify-center items-center"
                    style="min-width: 120px; height: 42px;">
                    Kirim Kode
                </button>
            </div>
            @error('email')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Input Kode Verifikasi --}}
        <div class="mb-6">
            <label for="code" class="block text-gray-700 font-semibold mb-1">Kode Verifikasi</label>
            <input id="code" type="text" name="code" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 @error('code') border-red-500 @enderror disabled:bg-gray-100"
                maxlength="6"
                style="text-align: center; font-size: 1.5rem; letter-spacing: 1rem;"
                placeholder="------"
                disabled
            />
            @error('code')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col items-center space-y-4">
            <button type="submit" id="verify-btn"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 disabled:bg-gray-400"
                disabled>
                Verifikasi & Lanjut
            </button>
            <a href="{{ route('login') }}" class="text-sm text-green-700 hover:underline">
                Kembali ke Login
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sendCodeBtn = document.getElementById('send-code-btn');
    const emailInput = document.getElementById('email');
    const codeInput = document.getElementById('code');
    const verifyBtn = document.getElementById('verify-btn');
    const notificationDiv = document.getElementById('notification');
    let cooldownTimer;

    sendCodeBtn.addEventListener('click', async function () {
        const email = emailInput.value;
        if (!email) {
            showNotification('Alamat email tidak boleh kosong.', 'error');
            return;
        }

        // TAMPILKAN LINGKARAN LOADING
        sendCodeBtn.disabled = true;
        sendCodeBtn.innerHTML = '<div class="animate-spin h-5 w-5 border-2 border-white rounded-full border-t-transparent"></div>';

        try {
            const response = await fetch("{{ route('admin.password.send.code') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email: email })
            });

            const data = await response.json();

            if (response.ok) {
                showNotification(data.message, 'success');
                codeInput.disabled = false;
                codeInput.focus();
                verifyBtn.disabled = false;
                startCooldown();
            } else {
                showNotification(data.message || 'Terjadi kesalahan.', 'error');
                sendCodeBtn.disabled = false;
                sendCodeBtn.innerHTML = 'Kirim Kode';
            }
        } catch (error) {
            showNotification('Gagal terhubung ke server.', 'error');
            sendCodeBtn.disabled = false;
            sendCodeBtn.innerHTML = 'Kirim Kode';
        }
    });

    function startCooldown() {
        let seconds = 60;
        sendCodeBtn.innerHTML = `Tunggu ${seconds}s`;

        cooldownTimer = setInterval(() => {
            seconds--;
            sendCodeBtn.innerHTML = `Tunggu ${seconds}s`;
            if (seconds <= 0) {
                clearInterval(cooldownTimer);
                sendCodeBtn.disabled = false;
                sendCodeBtn.innerHTML = 'Kirim Ulang';
            }
        }, 1000);
    }

    function showNotification(message, type) {
        notificationDiv.textContent = message;
        notificationDiv.className = 'mb-4 text-center font-semibold p-3 rounded-lg';
        if (type === 'success') {
            notificationDiv.classList.add('bg-green-100', 'text-green-700');
        } else {
            notificationDiv.classList.add('bg-red-100', 'text-red-700');
        }
    }
});
</script>
@endpush
