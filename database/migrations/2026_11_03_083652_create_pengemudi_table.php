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
        Schema::create('pengemudi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('no_kendaraan');
            $table->string('jenis_kendaraan');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengemudi');
    }
};
