<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Tampilkan daftar user
    public function index(Request $request)
    {
        $query = User::query();

        // Fitur pencarian (search by nama/email)
        if ($request->q) {
            $q = $request->q;
            $query->where(function($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                ->orWhere('email', 'like', "%$q%");
            });
        }

        // Urutkan terbaru
        $users = $query->orderBy('created_at', 'desc')->get();

        return view('admin.user.index', compact('users'));
    }

    // Tampilkan detail user
    public function show($id)
    {
        $user = User::findOrFail($id);

        // Jika ingin tampilkan riwayat transaksi user, relasikan di sini
        // $transactions = $user->transactions()->orderBy('created_at', 'desc')->get();

        return view('admin.user.show', compact('user'));
    }

    // Form edit user
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user.edit', compact('user'));
    }

    // Update user
    public function update(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'no_telp' => 'nullable|string|max:20',
            'username' => 'nullable|string|max:50',
            'role' => 'required|in:user,admin',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->no_telp = $request->no_telp;
        $user->username = $request->username;
        $user->role = $request->role;
        $user->save();

        return redirect()->route('admin.user.show', $user->id)->with('success', 'User berhasil diupdate.');
    }

    // Hapus user
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // === LOG AKTIVITAS HAPUS USER OLEH ADMIN ===
        LogAktivitas::create([
            'user_id'   => Auth::id(),
            'role'      => Auth::user()->role ?? 'admin',
            'aksi'      => 'hapus_user',
            'kategori'  => 'user', // kategori user
            'deskripsi' => 'Hapus user: ' . $user->name . ' (Email: ' . $user->email . ', ID: ' . $user->id . ')',
            'ip_address'=> request()->ip(),
        ]);

        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus.');
    }
}
