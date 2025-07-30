<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->integer('harga')->after('stock'); // setelah stock, integer (atau bisa decimal)
        });
    }
    public function down()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->dropColumn('harga');
        });
    }
};
