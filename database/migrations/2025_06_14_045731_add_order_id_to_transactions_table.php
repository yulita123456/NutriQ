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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('order_id')->after('id'); // setelah stock, integer (atau bisa decimal)
        });
    }
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('order_id');
        });
    }
};
