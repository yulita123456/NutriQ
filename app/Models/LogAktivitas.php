<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';
    protected $fillable = [
        'user_id', 'role', 'aksi', 'kategori', 'deskripsi', 'ip_address'
    ];

    // Relasi ke user (jika user/admin yang melakukan aksi)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
