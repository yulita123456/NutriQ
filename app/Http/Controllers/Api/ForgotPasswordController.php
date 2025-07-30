<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Meminta pengiriman kode reset ke email.
     * Endpoint: /forgot-password/request-code
     */
    public function requestCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Email tidak terdaftar.'], 404);
        }

        // Hapus token lama jika ada untuk email yang sama
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Buat kode acak 6 digit
        $code = random_int(100000, 999999);

        // Simpan kode ke database
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $code, // Kita simpan kode langsung, bukan hash
            'created_at' => Carbon::now()
        ]);

        // Kirim email (pastikan konfigurasi .env Anda sudah benar)
        try {
            Mail::raw("Kode verifikasi Anda untuk reset password adalah: $code", function ($message) use ($request) {
                $message->to($request->email)->subject('Kode Verifikasi Reset Password NutriQ');
            });
        } catch (\Exception $e) {
             // Jika gagal kirim email, beri tahu pengguna
             return response()->json(['message' => 'Gagal mengirim email. Silakan coba lagi.'], 500);
        }

        return response()->json(['message' => 'Kode verifikasi telah dikirim ke email Anda.'], 200);
    }

    /**
     * Memverifikasi kode yang dikirim pengguna.
     * Endpoint: /forgot-password/verify-code
     */
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $record = DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->where('token', $request->code)
                    ->first();

        // Cek jika record tidak ada atau kode salah
        if (!$record) {
            return response()->json(['message' => 'Kode verifikasi salah.'], 400);
        }

        // Cek jika kode sudah kedaluwarsa (misal: 10 menit)
        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            // Hapus token yang sudah expired agar tidak menumpuk
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Kode verifikasi sudah kedaluwarsa.'], 400);
        }

        return response()->json(['message' => 'Kode berhasil diverifikasi.'], 200);
    }

    /**
     * Mereset password pengguna setelah kode diverifikasi.
     * Endpoint: /forgot-password/reset
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|numeric', // Kode dibutuhkan lagi untuk keamanan
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Verifikasi ulang kodenya untuk memastikan prosesnya aman
        $record = DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->where('token', $request->code)
                    ->first();

        if (!$record) {
            return response()->json(['message' => 'Proses reset gagal. Kode tidak valid.'], 400);
        }

        // Cari user dan update passwordnya
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Hapus token setelah berhasil digunakan
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password berhasil direset. Silakan login.'], 200);
    }
}
