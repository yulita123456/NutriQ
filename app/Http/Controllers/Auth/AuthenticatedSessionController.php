<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Coba untuk login
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // ====================================================================
        // PERUBAHAN KUNCI ADA DI SINI
        // ====================================================================
        // Setelah login berhasil, cek role user
        $user = Auth::user();

        if ($user->role !== 'admin') {
            // Jika bukan admin, logout lagi
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Kirim pesan error spesifik
            throw ValidationException::withMessages([
                'email' => 'Anda tidak memiliki hak akses untuk halaman ini.',
            ]);
        }
        // ====================================================================
        // AKHIR DARI PERUBAHAN
        // ====================================================================


        // Jika lolos pengecekan role (adalah admin), baru lanjutkan
        $request->session()->regenerate();

        return redirect()->intended('/admin/dashboard');
    }
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
