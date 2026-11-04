<?php

namespace Database\Seeders;

use App\Models\Anak;
use App\Models\Jadwal;
use App\Models\LokasiDriver;
use App\Models\Notifikasi;
use App\Models\Pengemudi;
use App\Models\Penjemputan;
use App\Models\RiwayatStatus;
use App\Models\Sekolah;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = Hash::make('password');
        $today = Carbon::today();
        $dateStr = $today->format('Ymd');

        // 1. Akun Pengguna (Admin, Guru, Orang Tua, Pengemudi)
        $admin = User::create([
            'name' => 'Admin Armada Sekolah',
            'email' => 'admin@jemputaman.test',
            'password' => $password,
            'role' => 'admin',
            'no_hp' => '081200000001',
        ]);

        $guru = User::create([
            'name' => 'Ibu Ratna Dewi, S.Pd (Piket Sekolah)',
            'email' => 'guru@jemputaman.test',
            'password' => $password,
            'role' => 'guru',
            'no_hp' => '081200000002',
        ]);

        $ortu1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'ortu1@jemputaman.test',
            'password' => $password,
            'role' => 'orang_tua',
            'no_hp' => '081200000003',
        ]);

        $ortu2 = User::create([
            'name' => 'Siti Rahma',
            'email' => 'ortu2@jemputaman.test',
            'password' => $password,
            'role' => 'orang_tua',
            'no_hp' => '081200000004',
        ]);

        $driverUser1 = User::create([
            'name' => 'Pak Joko Widodo (Driver 1)',
            'email' => 'driver1@jemputaman.test',
            'password' => $password,
            'role' => 'pengemudi',
            'no_hp' => '081200000005',
        ]);

        $driver1 = Pengemudi::create([
            'user_id' => $driverUser1->id,
            'no_kendaraan' => 'AD 1234 JA',
            'jenis_kendaraan' => 'Isuzu Elf Sekolah 16 Seat',
            'aktif' => true,
        ]);

        $driverUser2 = User::create([
            'name' => 'Pak Bambang Hermanto (Driver 2)',
            'email' => 'driver2@jemputaman.test',
            'password' => $password,
            'role' => 'pengemudi',
            'no_hp' => '081200000006',
        ]);

        $driver2 = Pengemudi::create([
            'user_id' => $driverUser2->id,
            'no_kendaraan' => 'AD 5678 JA',
            'jenis_kendaraan' => 'Toyota HiAce Premio 14 Seat',
            'aktif' => true,
        ]);

        // 2. Data Master Sekolah Tunggal
        $sekolah = Sekolah::create([
            'nama' => 'SD JemputAman Surakarta',
            'alamat' => 'Jl. Slamet Riyadi No. 120, Kota Surakarta, Jawa Tengah',
        ]);

        // 3. Data Siswa (Anak) beserta Kode QR Unik
        $anak1 = Anak::create([
            'orang_tua_id' => $ortu1->id,
            'sekolah_id' => $sekolah->id,
            'nama' => 'Muhammad Rayyan Santoso',
            'kelas' => '3A',
            'kode_qr' => 'QR-RAYYAN-001',
        ]);

        $anak2 = Anak::create([
            'orang_tua_id' => $ortu1->id,
            'sekolah_id' => $sekolah->id,
            'nama' => 'Alesha Humaira Santoso',
            'kelas' => '1B',
            'kode_qr' => 'QR-ALESHA-002',
        ]);

        $anak3 = Anak::create([
            'orang_tua_id' => $ortu2->id,
            'sekolah_id' => $sekolah->id,
            'nama' => 'Kenzo Al-Ghifari Rahma',
            'kelas' => '4A',
            'kode_qr' => 'QR-KENZO-003',
        ]);

        $anak4 = Anak::create([
            'orang_tua_id' => $ortu2->id,
            'sekolah_id' => $sekolah->id,
            'nama' => 'Nadhira Putri Rahma',
            'kelas' => '2C',
            'kode_qr' => 'QR-NADHIRA-004',
        ]);

        // 4. Jadwal Rutin Antar-Jemput
        $jadwal1 = Jadwal::create([
            'anak_id' => $anak1->id,
            'pengemudi_id' => $driver1->id,
            'sekolah_id' => $sekolah->id,
            'hari' => 'Senin - Jumat',
            'jam_jemput' => '13:00',
            'titik_jemput' => 'Gerbang Utama SD JemputAman',
            'titik_tujuan' => 'Jl. Mawar No. 12, Laweyan, Surakarta',
            'aktif' => true,
        ]);

        $jadwal2 = Jadwal::create([
            'anak_id' => $anak2->id,
            'pengemudi_id' => $driver1->id,
            'sekolah_id' => $sekolah->id,
            'hari' => 'Senin - Jumat',
            'jam_jemput' => '13:00',
            'titik_jemput' => 'Gerbang Utama SD JemputAman',
            'titik_tujuan' => 'Jl. Mawar No. 12, Laweyan, Surakarta',
            'aktif' => true,
        ]);

        $jadwal3 = Jadwal::create([
            'anak_id' => $anak3->id,
            'pengemudi_id' => $driver2->id,
            'sekolah_id' => $sekolah->id,
            'hari' => 'Senin - Jumat',
            'jam_jemput' => '13:15',
            'titik_jemput' => 'Lobi Depan SD JemputAman',
            'titik_tujuan' => 'Jl. Melati No. 45, Banjarsari, Surakarta',
            'aktif' => true,
        ]);

        $jadwal4 = Jadwal::create([
            'anak_id' => $anak4->id,
            'pengemudi_id' => $driver2->id,
            'sekolah_id' => $sekolah->id,
            'hari' => 'Senin - Jumat',
            'jam_jemput' => '13:15',
            'titik_jemput' => 'Lobi Depan SD JemputAman',
            'titik_tujuan' => 'Jl. Melati No. 45, Banjarsari, Surakarta',
            'aktif' => true,
        ]);

        // 5. Transaksi Penjemputan Harian

        // Penjemputan 1: SELESAI (Rayyan) - Lengkap dengan scan QR, 2 foto bukti, GPS log, dan konfirmasi orang tua
        $p1 = Penjemputan::create([
            'kode_perjalanan' => 'JA-' . $dateStr . '-001',
            'jadwal_id' => $jadwal1->id,
            'anak_id' => $anak1->id,
            'pengemudi_id' => $driver1->id,
            'tanggal' => $today->toDateString(),
            'status' => Penjemputan::STATUS_SELESAI,
            'waktu_dijemput' => Carbon::now()->subMinutes(45),
            'waktu_berangkat' => Carbon::now()->subMinutes(40),
            'waktu_tiba' => Carbon::now()->subMinutes(15),
            'foto_serah_sekolah' => 'bukti_penjemputan/serah_rayyan_' . $dateStr . '.jpg',
            'foto_tiba' => 'bukti_penjemputan/tiba_rayyan_' . $dateStr . '.jpg',
            'waktu_konfirmasi_ortu' => Carbon::now()->subMinutes(10),
            'dikonfirmasi_oleh' => $ortu1->id,
            'catatan' => 'Siswa pulang tepat waktu, diterima langsung oleh ayah kandung.',
        ]);

        RiwayatStatus::create(['penjemputan_id' => $p1->id, 'diubah_oleh' => $admin->id, 'status' => 'dijadwalkan', 'waktu' => Carbon::now()->subHours(2), 'catatan' => 'Jadwal otomatis disiapkan sistem']);
        RiwayatStatus::create(['penjemputan_id' => $p1->id, 'diubah_oleh' => $driverUser1->id, 'status' => 'menuju_sekolah', 'waktu' => Carbon::now()->subMinutes(60), 'catatan' => 'Driver berangkat dari pool']);
        RiwayatStatus::create(['penjemputan_id' => $p1->id, 'diubah_oleh' => $driverUser1->id, 'status' => 'diserahkan', 'waktu' => Carbon::now()->subMinutes(45), 'catatan' => 'Scan QR siswa valid & foto serah terima diambil di gerbang sekolah']);
        RiwayatStatus::create(['penjemputan_id' => $p1->id, 'diubah_oleh' => $driverUser1->id, 'status' => 'dalam_perjalanan', 'waktu' => Carbon::now()->subMinutes(40), 'catatan' => 'Armada keluar dari area sekolah menuju rute rumah']);
        RiwayatStatus::create(['penjemputan_id' => $p1->id, 'diubah_oleh' => $driverUser1->id, 'status' => 'tiba', 'waktu' => Carbon::now()->subMinutes(15), 'catatan' => 'Armada tiba di rumah siswa, foto tiba diunggah']);
        RiwayatStatus::create(['penjemputan_id' => $p1->id, 'diubah_oleh' => $ortu1->id, 'status' => 'selesai', 'waktu' => Carbon::now()->subMinutes(10), 'catatan' => 'Orang tua memverifikasi anak telah sampai di rumah dengan aman']);

        // Log GPS Leaflet untuk Penjemputan 1
        LokasiDriver::create(['penjemputan_id' => $p1->id, 'latitude' => -7.5582000, 'longitude' => 110.8205000, 'dicatat_pada' => Carbon::now()->subMinutes(39)]);
        LokasiDriver::create(['penjemputan_id' => $p1->id, 'latitude' => -7.5610000, 'longitude' => 110.8235000, 'dicatat_pada' => Carbon::now()->subMinutes(30)]);
        LokasiDriver::create(['penjemputan_id' => $p1->id, 'latitude' => -7.5645000, 'longitude' => 110.8280000, 'dicatat_pada' => Carbon::now()->subMinutes(22)]);
        LokasiDriver::create(['penjemputan_id' => $p1->id, 'latitude' => -7.5670000, 'longitude' => 110.8320000, 'dicatat_pada' => Carbon::now()->subMinutes(15)]);

        // Penjemputan 2: DALAM PERJALANAN (Kenzo) - Live GPS tracking aktif di Leaflet
        $p2 = Penjemputan::create([
            'kode_perjalanan' => 'JA-' . $dateStr . '-002',
            'jadwal_id' => $jadwal3->id,
            'anak_id' => $anak3->id,
            'pengemudi_id' => $driver2->id,
            'tanggal' => $today->toDateString(),
            'status' => Penjemputan::STATUS_DALAM_PERJALANAN,
            'waktu_dijemput' => Carbon::now()->subMinutes(20),
            'waktu_berangkat' => Carbon::now()->subMinutes(15),
            'foto_serah_sekolah' => 'bukti_penjemputan/serah_kenzo_' . $dateStr . '.jpg',
            'catatan' => 'Siswa membawa perlengkapan pramuka.',
        ]);

        RiwayatStatus::create(['penjemputan_id' => $p2->id, 'diubah_oleh' => $admin->id, 'status' => 'dijadwalkan', 'waktu' => Carbon::now()->subHours(2), 'catatan' => 'Jadwal otomatis disiapkan sistem']);
        RiwayatStatus::create(['penjemputan_id' => $p2->id, 'diubah_oleh' => $driverUser2->id, 'status' => 'menuju_sekolah', 'waktu' => Carbon::now()->subMinutes(35), 'catatan' => 'Armada 2 siap di lobi sekolah']);
        RiwayatStatus::create(['penjemputan_id' => $p2->id, 'diubah_oleh' => $driverUser2->id, 'status' => 'diserahkan', 'waktu' => Carbon::now()->subMinutes(20), 'catatan' => 'Scan QR siswa sukses']);
        RiwayatStatus::create(['penjemputan_id' => $p2->id, 'diubah_oleh' => $driverUser2->id, 'status' => 'dalam_perjalanan', 'waktu' => Carbon::now()->subMinutes(15), 'catatan' => 'Sedang melintasi Jl. Gajah Mada']);

        // Log GPS Live untuk Penjemputan 2
        LokasiDriver::create(['penjemputan_id' => $p2->id, 'latitude' => -7.5582000, 'longitude' => 110.8205000, 'dicatat_pada' => Carbon::now()->subMinutes(15)]);
        LokasiDriver::create(['penjemputan_id' => $p2->id, 'latitude' => -7.5601000, 'longitude' => 110.8228000, 'dicatat_pada' => Carbon::now()->subMinutes(8)]);
        LokasiDriver::create(['penjemputan_id' => $p2->id, 'latitude' => -7.5628000, 'longitude' => 110.8252000, 'dicatat_pada' => Carbon::now()->subMinutes(1)]);

        // Penjemputan 3: TIBA (Alesha) - Menunggu konfirmasi penerimaan oleh orang tua
        $p3 = Penjemputan::create([
            'kode_perjalanan' => 'JA-' . $dateStr . '-003',
            'jadwal_id' => $jadwal2->id,
            'anak_id' => $anak2->id,
            'pengemudi_id' => $driver1->id,
            'tanggal' => $today->toDateString(),
            'status' => Penjemputan::STATUS_TIBA,
            'waktu_dijemput' => Carbon::now()->subMinutes(45),
            'waktu_berangkat' => Carbon::now()->subMinutes(40),
            'waktu_tiba' => Carbon::now()->subMinutes(5),
            'foto_serah_sekolah' => 'bukti_penjemputan/serah_alesha_' . $dateStr . '.jpg',
            'foto_tiba' => 'bukti_penjemputan/tiba_alesha_' . $dateStr . '.jpg',
            'catatan' => 'Siswa sudah sampai di pagar rumah, menunggu orang tua membuka aplikasi untuk konfirmasi.',
        ]);

        RiwayatStatus::create(['penjemputan_id' => $p3->id, 'diubah_oleh' => $admin->id, 'status' => 'dijadwalkan', 'waktu' => Carbon::now()->subHours(2), 'catatan' => 'Jadwal harian dibuat']);
        RiwayatStatus::create(['penjemputan_id' => $p3->id, 'diubah_oleh' => $driverUser1->id, 'status' => 'diserahkan', 'waktu' => Carbon::now()->subMinutes(45), 'catatan' => 'Scan QR siswa Alesha valid']);
        RiwayatStatus::create(['penjemputan_id' => $p3->id, 'diubah_oleh' => $driverUser1->id, 'status' => 'dalam_perjalanan', 'waktu' => Carbon::now()->subMinutes(40), 'catatan' => 'Dalam perjalanan']);
        RiwayatStatus::create(['penjemputan_id' => $p3->id, 'diubah_oleh' => $driverUser1->id, 'status' => 'tiba', 'waktu' => Carbon::now()->subMinutes(5), 'catatan' => 'Foto tiba diunggah']);

        // Penjemputan 4: DIJADWALKAN (Nadhira) - Perjalanan besok
        $p4 = Penjemputan::create([
            'kode_perjalanan' => 'JA-' . $today->copy()->addDay()->format('Ymd') . '-004',
            'jadwal_id' => $jadwal4->id,
            'anak_id' => $anak4->id,
            'pengemudi_id' => $driver2->id,
            'tanggal' => $today->copy()->addDay()->toDateString(),
            'status' => Penjemputan::STATUS_DIJADWALKAN,
            'catatan' => 'Jadwal reguler kepulangan besok siang.',
        ]);

        RiwayatStatus::create(['penjemputan_id' => $p4->id, 'diubah_oleh' => $admin->id, 'status' => 'dijadwalkan', 'waktu' => Carbon::now(), 'catatan' => 'Jadwal besok telah diterbitkan']);

        // 6. Notifikasi Dalam Aplikasi
        Notifikasi::create([
            'user_id' => $ortu1->id,
            'penjemputan_id' => $p1->id,
            'judul' => 'Serah Terima Selesai',
            'isi' => 'Ananda Muhammad Rayyan Santoso telah tiba dengan selamat dan serah terima telah dikonfirmasi.',
            'dibaca_at' => Carbon::now()->subMinutes(8),
        ]);

        Notifikasi::create([
            'user_id' => $ortu1->id,
            'penjemputan_id' => $p3->id,
            'judul' => 'Armada Tiba di Rumah',
            'isi' => 'Ananda Alesha Humaira Santoso telah tiba di rumah. Silakan periksa foto bukti dan lakukan konfirmasi penerimaan di aplikasi.',
            'dibaca_at' => null,
        ]);

        Notifikasi::create([
            'user_id' => $ortu2->id,
            'penjemputan_id' => $p2->id,
            'judul' => 'Armada Dalam Perjalanan',
            'isi' => 'Armada penjemputan Ananda Kenzo Al-Ghifari Rahma sedang dalam perjalanan. Pantau posisi live GPS pada peta.',
            'dibaca_at' => null,
        ]);

        Notifikasi::create([
            'user_id' => $guru->id,
            'penjemputan_id' => $p1->id,
            'judul' => 'Laporan Serah Terima Sekolah',
            'isi' => 'Penyerahan siswa di gerbang sekolah telah divalidasi oleh driver menggunakan scan kode QR.',
            'dibaca_at' => Carbon::now()->subMinutes(30),
        ]);
    }
}
