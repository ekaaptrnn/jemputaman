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
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->constrained('anak');
            $table->foreignId('pengemudi_id')->constrained('pengemudi');
            $table->foreignId('sekolah_id')->constrained('sekolah');
            $table->string('hari');
            $table->time('jam_jemput');
            $table->string('titik_jemput');
            $table->string('titik_tujuan');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};
