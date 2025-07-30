<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Nama tabel (opsional jika sesuai dengan konvensi Laravel)
    protected $table = 'product';

    // Kolom-kolom yang bisa diisi (mass assignable)
    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'kategori',
        'harga',
        'kalori',
        'lemak_total',
        'lemak_jenuh',
        'protein',
        'gula',
        'karbohidrat',
        'garam',
        'foto',
        'status',
        'foto_gizi',
        'stock',
    ];

    protected $casts = [
        'foto' => 'array', // otomatis decode JSON jadi array
    ];

    public function getFotoAttribute($value)
{
    return json_decode($value, true) ?? [];
}
public function persenAkg()
{
    $akg = config('akg');

    // Konversi garam dari mg ke gram sebelum perhitungan
    $garamInGrams = $this->garam / 1000;

    return [
        'kalori'      => $this->kalori > 0 ? round(($this->kalori / $akg['kalori']) * 100, 1) : 0,
        'protein'     => $this->protein > 0 ? round(($this->protein / $akg['protein']) * 100, 1) : 0,
        'lemak_total' => $this->lemak_total > 0 ? round(($this->lemak_total / $akg['lemak_total']) * 100, 1) : 0,
        'lemak_jenuh' => $this->lemak_jenuh > 0 ? round(($this->lemak_jenuh / $akg['lemak_jenuh']) * 100, 1) : 0,
        'karbohidrat' => $this->karbohidrat > 0 ? round(($this->karbohidrat / $akg['karbohidrat']) * 100, 1) : 0,
        'gula'        => $this->gula > 0 ? round(($this->gula / $akg['gula']) * 100, 1) : 0,
        'garam'       => $this->garam > 0 ? round(($garamInGrams / $akg['garam']) * 100, 1) : 0, // <-- PERUBAHAN DI SINI
    ];
}

public function isSehat()
{
    $persen = $this->persenAkg();
    $batas = config('akg.batas_sehat', 20); // Default ke 20% jika tidak diatur

    // Hanya 3 komponen utama yang dinilai
    return ($persen['gula'] <= $batas)
        && ($persen['garam'] <= $batas)
        && ($persen['lemak_jenuh'] <= $batas);
}

public function hasNutritionData()
{
    // Cek beberapa field kunci. Jika semuanya 0, anggap data tidak ada.
    // Mengembalikan true jika ADA data, dan false jika TIDAK ADA.
    return $this->kalori > 0 || $this->lemak_total > 0 || $this->protein > 0;
}
}
