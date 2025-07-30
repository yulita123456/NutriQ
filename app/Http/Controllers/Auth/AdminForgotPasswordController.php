<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class AdminForgotPasswordController extends Controller
{
    // Menampilkan form gabungan
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    // Mengirim kode ke email
    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->where('role', 'admin')->first();

        if (!$user) {
            // Kembalikan error sebagai JSON
            return response()->json(['message' => 'Kami tidak dapat menemukan admin dengan alamat email tersebut.'], 404);
        }

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        $code = random_int(100000, 999999);
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $code,
            'created_at' => Carbon::now()
        ]);

        Mail::raw("Kode verifikasi Anda untuk reset password admin adalah: $code", function ($message) use ($request) {
            $message->to($request->email)->subject('Kode Verifikasi Reset Password Admin NutriQ');
        });

        // Kembalikan pesan sukses sebagai JSON
        return response()->json(['message' => 'Kode verifikasi telah berhasil dikirim ke email Anda.']);
    }
    // Memverifikasi kode
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|numeric'
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        if (!$record || Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            // Jika gagal, redirect kembali dengan pesan error
            return redirect()->route('admin.password.request')
                             ->with('email_sent', $request->email)
                             ->withErrors(['code' => 'Kode verifikasi tidak valid atau sudah kedaluwarsa.']);
        }

        // Jika berhasil, arahkan ke halaman ganti password baru
        return redirect()->route('admin.password.reset')->with([
            'email' => $request->email,
            'code'  => $request->code
        ]);
    }

    // Dua fungsi ini tidak berubah, biarkan saja
    public function showResetForm()
    {
        if (!session('email') || !session('code')) {
            return redirect()->route('admin.password.request');
        }
        return view('auth.reset-password-form'); // Pastikan nama view ini benar
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'code'     => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        // Verifikasi ulang token/code untuk keamanan
        $record = DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->where('token', 'like', $request->code)
                    ->first();

        if (!$record) {
             return redirect()->route('admin.password.request')->withErrors(['email' => 'Sesi reset password tidak valid. Silakan ulangi proses.']);
        }

        // Update password user
        User::where('email', $request->email)->update(['password' => Hash::make($request->password)]);

        // Hapus token setelah digunakan
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Redirect ke halaman login dengan pesan sukses
        return redirect()->route('login')->with('status', 'Password Anda berhasil direset!');
    }
}
