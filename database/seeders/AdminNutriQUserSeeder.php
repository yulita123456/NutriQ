<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // <<< Pastikan ini di-import!
use Carbon\Carbon; // Pastikan ini ada jika menggunakan Carbon::now()

class AdminNutriQUserSeeder extends Seeder
{
    /**
     * Jalankan database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Peringatan: Ini akan menyebabkan error jika user sudah ada
        // dan email atau username memiliki constraint UNIQUE di database.
        // Jika terjadi error 'Duplicate entry', deployment akan GAGAL.

        User::create([
            'name' => 'Yulita Nur Fathin',
            'email' => 'yulitanurfathin@gmail.com', // Pastikan email ini unik
            'password' => Hash::make('admin123'), // Ini akan selalu di-hash ulang
            'username' => 'Admin', // Pastikan username ini unik
            'no_telp' => '0826728681697',
            'alamat' => null,
            'role' => 'admin',
            'created_at' => Carbon::now(), // Gunakan Carbon jika perlu timestamps
            'updated_at' => Carbon::now(), // Gunakan Carbon jika perlu timestamps
        ]);
        // Anda bisa tambahkan log di sini jika ingin melihat output di log Railway saat seeder dijalankan
        // \Log::info('Admin user yulitanurfathin@gmail.com created successfully using User::create().');
    }
}
