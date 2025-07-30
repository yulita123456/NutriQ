<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    // Jika nama tabel sesuai konvensi (transactions), ini bisa dihilangkan:
    // protected $table = 'transactions';

    protected $fillable = [
        'user_id',
        'total',
        'status',
        'alamat',
        'catatan',
        'order_id',
        'snap_token',
        'redirect_url',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Detail Transaksi
    public function details()
    {
        return $this->hasMany(\App\Models\TransactionDetail::class, 'transaction_id');
    }
}
