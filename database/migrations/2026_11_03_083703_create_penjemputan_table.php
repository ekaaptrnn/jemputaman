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
        Schema::create('penjemputan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_perjalanan')->unique();
            $table->foreignId('jadwal_id')->constrained('jadwal');
            $table->foreignId('anak_id')->constrained('anak');
            $table->foreignId('pengemudi_id')->constrained('pengemudi');
            $table->foreignId('penjemput_id')->constrained('pihak_berwenang');
            $table->foreignId('penerima_id')->constrained('pihak_berwenang');
            $table->date('tanggal');
            $table->string('status')->default('menunggu');
            $table->string('kode_verifikasi', 6);
            $table->dateTime('waktu_dijemput')->nullable();
            $table->dateTime('waktu_berangkat')->nullable();
            $table->dateTime('waktu_tiba')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjemputan');
    }
};
