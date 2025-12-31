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
        Schema::table('convidados', function (Blueprint $table) {
            $table->string('foto_s3_key', 512)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('convidados', function (Blueprint $table) {
            $table->dropColumn('foto_s3_key');
        });
    }
};
