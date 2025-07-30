<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatScan extends Model
{
    use HasFactory;

    protected $table = 'riwayat_scan';
    protected $fillable = [
        'user_id',
        'produk_id',
        'is_sehat',
        'waktu_scan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produk()
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }
}
