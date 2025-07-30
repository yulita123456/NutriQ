<?php


namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;

class PengaturanController extends Controller
{
    public function index()
    {
        return view('admin.pengaturan.index');
    }

    public function edit()
{
    $user = Auth::user();
    return view('admin.pengaturan.edit', compact('user'));
}

public function update(Request $request)
{
    $user = Auth::user();
    $request->validate([
        'name' => 'required|string|max:50',
        'email' => 'required|email|max:100|unique:users,email,'.$user->id,
        'username' => 'required|string|max:30|unique:users,username,'.$user->id,
        'no_telp' => 'nullable|string|max:20',
        'alamat' => 'nullable|string|max:200',
        'password' => 'nullable|string|min:6|confirmed',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;
    $user->username = $request->username;
    $user->no_telp = $request->no_telp;
    $user->alamat = $request->alamat;
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }
    $user->save();

    return redirect()->route('admin.pengaturan.edit')->with('success', 'Profil berhasil diperbarui!');
}
}
