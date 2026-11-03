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
        Schema::create('pihak_berwenang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->constrained('anak')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama');
            $table->string('hubungan');
            $table->string('tipe'); // penjemput atau penerima
            $table->string('no_hp')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pihak_berwenang');
    }
};
