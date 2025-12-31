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
        Schema::create('convidados_access_token', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained('convidados')->onDelete('cascade');
            $table->string('token', 512);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convidados_access_token');
    }
};
