<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKategoriToLogAktivitasTable extends Migration
{
    public function up()
    {
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('aksi')->index();
        });
    }

    public function down()
    {
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
}
