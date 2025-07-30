<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        // Tabel transaksi utama
        Schema::create('transactions', function (Blueprint $table) {
            $table->id(); // id
            $table->string('order_id')->unique(); // order_id
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('total');
            $table->string('status')->default('pending'); // pending, settlement, failed
            $table->string('alamat')->nullable();
            $table->text('catatan')->nullable();
            $table->string('snap_token')->nullable(); // snap_token
            $table->string('redirect_url')->nullable(); // redirect_url
            $table->timestamps(); // created_at, updated_at
        });

        // Tabel detail transaksi
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('product')->onDelete('cascade');
            $table->integer('qty');
            $table->integer('harga');
            $table->integer('subtotal');
            $table->timestamps();
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
        Schema::dropIfExists('transactions');
    }
};
