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
        Schema::table('product', function (Blueprint $table) {
            $table->integer('kalori')->nullable()->default(0)->change();
            $table->decimal('lemak_total', 8, 2)->nullable()->default(0)->change();
            $table->decimal('lemak_jenuh', 8, 2)->nullable()->default(0)->change();
            $table->decimal('protein', 8, 2)->nullable()->default(0)->change();
            $table->decimal('gula', 8, 2)->nullable()->default(0)->change();
            $table->decimal('karbohidrat', 8, 2)->nullable()->default(0)->change();
            $table->decimal('garam', 8, 2)->nullable()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product', function (Blueprint $table) {
            //
        });
    }
};
