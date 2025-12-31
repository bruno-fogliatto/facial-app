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
        Schema::table('convidados_access_token', function (Blueprint $table) {
            $table->string('biometry_link', 512)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('convidados_access_token', function (Blueprint $table) {
            $table->dropColumn('biometry_link');    
        });
    }
};
