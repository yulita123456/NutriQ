<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AdminNutriQUserSeeder::class, // Panggil seeder admin NutriQ di sini
            // Anda bisa menambahkan seeder lain di sini jika ada (misal: ProductSeeder, etc.)
        ]);
    }
}
