<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminNutriQUserSeeder extends Seeder
{
    /**
     * Jalankan database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->updateOrCreate(
            [
                'email' => 'yulitanurfathin@gmail.com', // Kunci pencarian: pastikan email ini unik untuk admin
            ],
            [
                'name' => 'Yulita Nur Fathin',
                'password' => Hash::make('admin123'), // Ini akan selalu di-hash ulang atau di-update jika ada
                'username' => 'Admin', // Pastikan username ini juga unik dan belum dipakai user lain
                'no_telp' => '0826728681697',
                'alamat' => null,
                'role' => 'admin',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}
