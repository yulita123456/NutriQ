<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
{
    $search = $request->input('search');
    $role = $request->input('role');
    $kategori = $request->input('kategori');
    $from = $request->input('from');
    $to = $request->input('to');

    $query = LogAktivitas::with('user')->orderBy('created_at', 'desc');

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('aksi', 'like', "%{$search}%")
              ->orWhere('deskripsi', 'like', "%{$search}%");
        });
    }

    if ($role) {
        $query->where('role', $role);
    }

    if ($kategori) {
        // Filter berdasarkan kolom kategori langsung
        $query->where('kategori', $kategori);
    }

    if ($from) {
        $query->whereDate('created_at', '>=', $from);
    }
    if ($to) {
        $query->whereDate('created_at', '<=', $to);
    }

    $logs = $query->paginate(15);

    return view('admin.log_aktivitas.index', compact('logs'));
}

    public function show($id)
    {
        $log = LogAktivitas::with('user')->findOrFail($id);

        return response()->json($log);
    }
}
