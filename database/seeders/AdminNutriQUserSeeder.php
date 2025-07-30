<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon; // Pastikan Carbon di-import

class AdminNutriQUserSeeder extends Seeder
{
    /**
     * Jalankan database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Hapus user dengan email/username ini terlebih dahulu jika sudah ada,
        // untuk mencegah duplikasi saat seeder dijalankan berulang.
        DB::table('users')->where('email', 'yulitanurfathin@gmail.com')->delete();
        // Atau DB::table('users')->where('username', 'Admin')->delete();
        // Pilih salah satu yang paling unik untuk identifikasi data admin

        DB::table('users')->insert([
            'id' => 1, // ID ini bisa dihilangkan jika ingin AUTO_INCREMENT otomatis
            'name' => 'Yulita Nur Fathin',
            'email' => 'yulitanurfathin@gmail.com',
            'email_verified_at' => null,
            'password' => '$2y$12$T6E89j9J88/h.MFqOWJbwONop7cyvWebX9DZ6xNpGfkf3f4HpObf2', // Password sudah di-hash
            'username' => 'Admin',
            'no_telp' => '0826728681697',
            'alamat' => null, // Sesuai data yang Anda berikan
            'role' => 'admin',
            'created_at' => Carbon::now(), // Menggunakan waktu saat seeder dijalankan
            'updated_at' => Carbon::now(), // Menggunakan waktu saat seeder dijalankan
        ]);
    }
}
