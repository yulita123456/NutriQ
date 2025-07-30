<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogAktivitasTable extends Migration
{
    public function up()
    {
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('role', 20)->nullable(); // 'admin' atau 'user'
            $table->string('aksi', 50);             // 'input_produk', 'edit_produk', 'hapus_produk', 'login', 'register', dll
            $table->text('deskripsi')->nullable();  // penjelasan/detail aksi
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down()
    {
        Schema::dropIfExists('log_aktivitas');
    }
}
