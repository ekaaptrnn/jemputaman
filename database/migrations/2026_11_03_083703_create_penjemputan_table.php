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
            $table->foreignId('jadwal_id')->constrained('jadwal')->cascadeOnDelete();
            $table->foreignId('anak_id')->constrained('anak')->cascadeOnDelete();
            $table->foreignId('pengemudi_id')->constrained('pengemudi')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('status')->default('dijadwalkan');
            $table->dateTime('waktu_dijemput')->nullable();
            $table->dateTime('waktu_berangkat')->nullable();
            $table->dateTime('waktu_tiba')->nullable();
            $table->string('foto_serah_sekolah')->nullable();
            $table->string('foto_tiba')->nullable();
            $table->dateTime('waktu_konfirmasi_ortu')->nullable();
            $table->foreignId('dikonfirmasi_oleh')->nullable()->constrained('users')->nullOnDelete();
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
