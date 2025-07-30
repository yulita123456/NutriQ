<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\LogAktivitas;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Login gagal'], 401);
        }

        $user = Auth::user();

        // === LOG AKTIVITAS LOGIN USER ===
        LogAktivitas::create([
            'user_id'   => $user->id,
            'role'      => $user->role ?? 'user',
            'aksi'      => 'login',
            'kategori'  => 'user',
            'deskripsi' => 'Login user: ' . $user->email . ' (username: ' . $user->username . ')',
            'ip_address'=> $request->ip(),
        ]);

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'username' => 'required|string|unique:users',
            'password' => 'required|min:6',
            'no_telp' => 'required',
            'alamat' => 'required',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'no_telp' => $validated['no_telp'],
            'alamat' => $validated['alamat'],
            'role' => 'user',
        ]);

        // === LOG AKTIVITAS REGISTRASI USER ===
        LogAktivitas::create([
            'user_id'   => $user->id,
            'role'      => 'user',
            'aksi'      => 'register',
            'kategori'  => 'user',
            'deskripsi' => 'Register akun: ' . $user->email . ' (username: ' . $user->username . ')',
            'ip_address'=> $request->ip(),
        ]);

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => $user,
            'token' => $token,
        ], 201);
    }
    public function profile(Request $request)
    {
        $user = $request->user();

        // Ambil riwayat scan user
        $riwayat = \DB::table('riwayat_scan')
            ->join('product', 'riwayat_scan.produk_id', '=', 'product.id')
            ->where('riwayat_scan.user_id', $user->id)
            ->orderBy('riwayat_scan.waktu_scan', 'desc')
            ->select(
                'riwayat_scan.id',
                'product.nama_produk',
                'riwayat_scan.waktu_scan',
                'riwayat_scan.is_sehat'
            )
            ->get();

        return response()->json([
            'user' => [
                'name'      => $user->name,
                'email'     => $user->email,
                'username'  => $user->username,
                'no_telp'   => $user->no_telp,
                'alamat'    => $user->alamat,
                'created_at'=> $user->created_at,
            ],
            'riwayat_scan' => $riwayat,
        ]);
    }
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'no_telp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:255',
        ]);

        // Simpan data lama sebelum update
        $oldData = $user->toArray();

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'no_telp' => $request->no_telp,
            'alamat' => $request->alamat,
        ]);

        // Deteksi perubahan field penting (nama, email, username, no_telp, alamat)
        $changes = [];
        foreach (['name', 'email', 'username', 'no_telp', 'alamat'] as $field) {
            if ($oldData[$field] != $request->$field) {
                $changes[] = ucfirst($field) . ': ' . $oldData[$field] . ' → ' . $request->$field;
            }
        }
        $changesStr = $changes ? ' (' . implode('; ', $changes) . ')' : '';

        // === LOG AKTIVITAS UPDATE PROFILE ===
        LogAktivitas::create([
            'user_id'   => $user->id,
            'role'      => $user->role ?? 'user',
            'aksi'      => 'update_profil',
            'kategori'  => 'user',
            'deskripsi' => 'Update profil user: ' . $user->email . $changesStr,
            'ip_address'=> $request->ip(),
        ]);

        return response()->json([
            'message' => 'Profil berhasil diperbarui!',
            'user' => $user
        ]);
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!\Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama salah!'
            ], 422);
        }

        $user->password = bcrypt($request->new_password);
        $user->save();

        // === LOG AKTIVITAS GANTI PASSWORD ===
        LogAktivitas::create([
            'user_id'   => $user->id,
            'role'      => $user->role ?? 'user',
            'aksi'      => 'ganti_password',
            'kategori'  => 'user',

            'deskripsi' => 'User mengganti password akun: ' . $user->email,
            'ip_address'=> $request->ip(),
        ]);

        return response()->json(['message' => 'Password berhasil diganti!']);
    }
}
