<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    // Jika nama tabel sesuai konvensi (transaction_details), ini bisa dihilangkan:
    // protected $table = 'transaction_details';

    protected $fillable = [
        'transaction_id',
        'product_id',
        'qty',
        'harga',
        'subtotal',
    ];

    // Relasi ke Transaksi
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Relasi ke Produk
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id');
    }
}
