<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product', function (Blueprint $table) {
            $table->id();
            $table->string('kode_produk')->unique();
            $table->string('nama_produk');
            $table->integer('stock');
            $table->integer('kalori');
            $table->float('lemak_total');
            $table->float('lemak_jenuh');
            $table->float('protein');
            $table->float('gula');
            $table->float('karbohidrat');
            $table->float('garam');
            $table->text('foto')->nullable();
            $table->text('foto_gizi')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
