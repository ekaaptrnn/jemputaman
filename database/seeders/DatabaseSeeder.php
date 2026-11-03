<?php

namespace Database\Seeders;

use App\Models\Anak;
use App\Models\Jadwal;
use App\Models\Notifikasi;
use App\Models\Pengemudi;
use App\Models\Penjemputan;
use App\Models\PihakBerwenang;
use App\Models\RiwayatStatus;
use App\Models\Sekolah;
use App\Models\SerahTerima;
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
        // 1. Akun pengguna tetap (password: password)
        $password = Hash::make('password');

        $admin = User::create([
            'name' => 'Admin JemputAman',
            'email' => 'admin@jemputaman.test',
            'password' => $password,
            'role' => 'admin',
            'no_hp' => '081200000001',
        ]);

        $ortu1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'ortu1@jemputaman.test',
            'password' => $password,
            'role' => 'orang_tua',
            'no_hp' => '081200000002',
        ]);

        $ortu2 = User::create([
            'name' => 'Siti Rahma',
            'email' => 'ortu2@jemputaman.test',
            'password' => $password,
            'role' => 'orang_tua',
            'no_hp' => '081200000003',
        ]);

        $driverUser1 = User::create([
            'name' => 'Pak Joko Widodo (Driver 1)',
            'email' => 'driver1@jemputaman.test',
            'password' => $password,
            'role' => 'pengemudi',
            'no_hp' => '081200000004',
        ]);

        $driver1 = Pengemudi::create([
            'user_id' => $driverUser1->id,
            'no_kendaraan' => 'B 1234 ABC',
            'jenis_kendaraan' => 'Mobil Avanza',
            'aktif' => true,
        ]);

        $driverUser2 = User::create([
            'name' => 'Pak Bambang (Driver 2)',
            'email' => 'driver2@jemputaman.test',
            'password' => $password,
            'role' => 'pengemudi',
            'no_hp' => '081200000005',
        ]);

        $driver2 = Pengemudi::create([
            'user_id' => $driverUser2->id,
            'no_kendaraan' => 'B 5678 XYZ',
            'jenis_kendaraan' => 'Motor Vario',
            'aktif' => true,
        ]);

        $penerimaUser1 = User::create([
            'name' => 'Nenek Maryam',
            'email' => 'penerima1@jemputaman.test',
            'password' => $password,
            'role' => 'penerima',
            'no_hp' => '081200000006',
        ]);

        $penerimaUser2 = User::create([
            'name' => 'Paman Dani',
            'email' => 'penerima2@jemputaman.test',
            'password' => $password,
            'role' => 'penerima',
            'no_hp' => '081200000007',
        ]);

        // 2. Data Sekolah (2 sekolah)
        $sekolah1 = Sekolah::create([
            'nama' => 'SD Negeri Mentari 01',
            'alamat' => 'Jl. Pendidikan No. 12, Jakarta Selatan',
        ]);

        $sekolah2 = Sekolah::create([
            'nama' => 'SDIT Insan Mulia',
            'alamat' => 'Jl. Melati No. 45, Jakarta Selatan',
        ]);

        // 3. Data Anak (4 anak: ortu1 punya 2 anak, ortu2 punya 2 anak)
        $anak1 = Anak::create([
            'orang_tua_id' => $ortu1->id,
            'sekolah_id' => $sekolah1->id,
            'nama' => 'Kenzo Pratama',
            'kelas' => '3A',
        ]);

        $anak2 = Anak::create([
            'orang_tua_id' => $ortu1->id,
            'sekolah_id' => $sekolah1->id,
            'nama' => 'Alina Putri',
            'kelas' => '1B',
        ]);

        $anak3 = Anak::create([
            'orang_tua_id' => $ortu2->id,
            'sekolah_id' => $sekolah2->id,
            'nama' => 'Rizky Ramadhan',
            'kelas' => '4C',
        ]);

        $anak4 = Anak::create([
            'orang_tua_id' => $ortu2->id,
            'sekolah_id' => $sekolah2->id,
            'nama' => 'Zahra Aulia',
            'kelas' => '2A',
        ]);

        // 4. Pihak Berwenang per anak:
        // 1 penjemput (tanpa akun: user_id = null), 1 penerima (ortu1 -> penerima1, ortu2 -> penerima2)
        $pb1_jemput = PihakBerwenang::create([
            'anak_id' => $anak1->id,
            'user_id' => null,
            'nama' => 'Kakek Ahmad',
            'hubungan' => 'Kakek',
            'tipe' => 'penjemput',
            'no_hp' => '082100000001',
            'aktif' => true,
        ]);

        $pb1_terima = PihakBerwenang::create([
            'anak_id' => $anak1->id,
            'user_id' => $penerimaUser1->id,
            'nama' => 'Nenek Maryam',
            'hubungan' => 'Nenek',
            'tipe' => 'penerima',
            'no_hp' => '082100000002',
            'aktif' => true,
        ]);

        $pb2_jemput = PihakBerwenang::create([
            'anak_id' => $anak2->id,
            'user_id' => null,
            'nama' => 'Paman Bagas',
            'hubungan' => 'Paman',
            'tipe' => 'penjemput',
            'no_hp' => '082100000003',
            'aktif' => true,
        ]);

        $pb2_terima = PihakBerwenang::create([
            'anak_id' => $anak2->id,
            'user_id' => $penerimaUser1->id,
            'nama' => 'Nenek Maryam',
            'hubungan' => 'Nenek',
            'tipe' => 'penerima',
            'no_hp' => '082100000002',
            'aktif' => true,
        ]);

        $pb3_jemput = PihakBerwenang::create([
            'anak_id' => $anak3->id,
            'user_id' => null,
            'nama' => 'Kakek Suryo',
            'hubungan' => 'Kakek',
            'tipe' => 'penjemput',
            'no_hp' => '082100000004',
            'aktif' => true,
        ]);

        $pb3_terima = PihakBerwenang::create([
            'anak_id' => $anak3->id,
            'user_id' => $penerimaUser2->id,
            'nama' => 'Paman Dani',
            'hubungan' => 'Paman',
            'tipe' => 'penerima',
            'no_hp' => '082100000005',
            'aktif' => true,
        ]);

        $pb4_jemput = PihakBerwenang::create([
            'anak_id' => $anak4->id,
            'user_id' => null,
            'nama' => 'Bibi Ratna',
            'hubungan' => 'Bibi',
            'tipe' => 'penjemput',
            'no_hp' => '082100000006',
            'aktif' => true,
        ]);

        $pb4_terima = PihakBerwenang::create([
            'anak_id' => $anak4->id,
            'user_id' => $penerimaUser2->id,
            'nama' => 'Paman Dani',
            'hubungan' => 'Paman',
            'tipe' => 'penerima',
            'no_hp' => '082100000005',
            'aktif' => true,
        ]);

        // 5. Jadwal untuk tiap anak (hari & jam berbeda, driver1 atau driver2)
        $jadwal1 = Jadwal::create([
            'anak_id' => $anak1->id,
            'pengemudi_id' => $driver1->id,
            'sekolah_id' => $sekolah1->id,
            'hari' => 'Senin',
            'jam_jemput' => '13:00:00',
            'titik_jemput' => 'Gerbang SD Negeri Mentari 01',
            'titik_tujuan' => 'Rumah Nenek Maryam (Jl. Anggrek No. 5)',
            'aktif' => true,
        ]);

        $jadwal2 = Jadwal::create([
            'anak_id' => $anak2->id,
            'pengemudi_id' => $driver1->id,
            'sekolah_id' => $sekolah1->id,
            'hari' => 'Selasa',
            'jam_jemput' => '12:00:00',
            'titik_jemput' => 'Lobby Depan SD Negeri Mentari 01',
            'titik_tujuan' => 'Rumah Nenek Maryam (Jl. Anggrek No. 5)',
            'aktif' => true,
        ]);

        $jadwal3 = Jadwal::create([
            'anak_id' => $anak3->id,
            'pengemudi_id' => $driver2->id,
            'sekolah_id' => $sekolah2->id,
            'hari' => 'Rabu',
            'jam_jemput' => '14:30:00',
            'titik_jemput' => 'Pos Satpam SDIT Insan Mulia',
            'titik_tujuan' => 'Rumah Paman Dani (Jl. Kenanga No. 18)',
            'aktif' => true,
        ]);

        $jadwal4 = Jadwal::create([
            'anak_id' => $anak4->id,
            'pengemudi_id' => $driver2->id,
            'sekolah_id' => $sekolah2->id,
            'hari' => 'Kamis',
            'jam_jemput' => '13:30:00',
            'titik_jemput' => 'Gerbang Timur SDIT Insan Mulia',
            'titik_tujuan' => 'Rumah Paman Dani (Jl. Kenanga No. 18)',
            'aktif' => true,
        ]);

        // 6. 8 Penjemputan:
        // 2 menunggu, 2 dijemput, 2 dalam_perjalanan, 1 tiba, 1 tiba + serah_terima
        $today = Carbon::today()->toDateString();
        $baseTime = Carbon::today();

        // 1. Menunggu (demo kode verifikasi 123456)
        $p1 = Penjemputan::create([
            'kode_perjalanan' => 'JA-20261103-001',
            'jadwal_id' => $jadwal1->id,
            'anak_id' => $anak1->id,
            'pengemudi_id' => $driver1->id,
            'penjemput_id' => $pb1_jemput->id,
            'penerima_id' => $pb1_terima->id,
            'tanggal' => $today,
            'status' => 'menunggu',
            'kode_verifikasi' => '123456',
            'waktu_dijemput' => null,
            'waktu_berangkat' => null,
            'waktu_tiba' => null,
            'catatan' => 'Menunggu waktu penjemputan tiba.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p1->id,
            'diubah_oleh' => $admin->id,
            'status' => 'menunggu',
            'waktu' => $baseTime->copy()->setHour(7)->setMinute(0),
            'catatan' => 'Jadwal penjemputan berhasil dibuat oleh admin.',
        ]);

        // 2. Menunggu
        $p2 = Penjemputan::create([
            'kode_perjalanan' => 'JA-20261103-002',
            'jadwal_id' => $jadwal3->id,
            'anak_id' => $anak3->id,
            'pengemudi_id' => $driver2->id,
            'penjemput_id' => $pb3_jemput->id,
            'penerima_id' => $pb3_terima->id,
            'tanggal' => $today,
            'status' => 'menunggu',
            'kode_verifikasi' => '654321',
            'waktu_dijemput' => null,
            'waktu_berangkat' => null,
            'waktu_tiba' => null,
            'catatan' => 'Menunggu keberangkatan driver menuju sekolah.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p2->id,
            'diubah_oleh' => $admin->id,
            'status' => 'menunggu',
            'waktu' => $baseTime->copy()->setHour(7)->setMinute(15),
            'catatan' => 'Penjemputan dijadwalkan.',
        ]);

        // 3. Dijemput
        $p3 = Penjemputan::create([
            'kode_perjalanan' => 'JA-20261103-003',
            'jadwal_id' => $jadwal2->id,
            'anak_id' => $anak2->id,
            'pengemudi_id' => $driver1->id,
            'penjemput_id' => $pb2_jemput->id,
            'penerima_id' => $pb2_terima->id,
            'tanggal' => $today,
            'status' => 'dijemput',
            'kode_verifikasi' => '234567',
            'waktu_dijemput' => $baseTime->copy()->setHour(12)->setMinute(5),
            'waktu_berangkat' => null,
            'waktu_tiba' => null,
            'catatan' => 'Anak telah dijemput di lobby depan.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p3->id,
            'diubah_oleh' => $admin->id,
            'status' => 'menunggu',
            'waktu' => $baseTime->copy()->setHour(7)->setMinute(30),
            'catatan' => 'Penjemputan terdaftar.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p3->id,
            'diubah_oleh' => $driverUser1->id,
            'status' => 'dijemput',
            'waktu' => $baseTime->copy()->setHour(12)->setMinute(5),
            'catatan' => 'Pengemudi telah bertemu dengan pihak sekolah dan anak.',
        ]);

        // 4. Dijemput
        $p4 = Penjemputan::create([
            'kode_perjalanan' => 'JA-20261103-004',
            'jadwal_id' => $jadwal4->id,
            'anak_id' => $anak4->id,
            'pengemudi_id' => $driver2->id,
            'penjemput_id' => $pb4_jemput->id,
            'penerima_id' => $pb4_terima->id,
            'tanggal' => $today,
            'status' => 'dijemput',
            'kode_verifikasi' => '345678',
            'waktu_dijemput' => $baseTime->copy()->setHour(13)->setMinute(35),
            'waktu_berangkat' => null,
            'waktu_tiba' => null,
            'catatan' => 'Anak telah bersama pengemudi di gerbang timur.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p4->id,
            'diubah_oleh' => $admin->id,
            'status' => 'menunggu',
            'waktu' => $baseTime->copy()->setHour(7)->setMinute(45),
            'catatan' => 'Penjemputan terdaftar.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p4->id,
            'diubah_oleh' => $driverUser2->id,
            'status' => 'dijemput',
            'waktu' => $baseTime->copy()->setHour(13)->setMinute(35),
            'catatan' => 'Anak dijemput dari gerbang timur sekolah.',
        ]);

        // 5. Dalam Perjalanan
        $p5 = Penjemputan::create([
            'kode_perjalanan' => 'JA-20261103-005',
            'jadwal_id' => $jadwal1->id,
            'anak_id' => $anak1->id,
            'pengemudi_id' => $driver1->id,
            'penjemput_id' => $pb1_jemput->id,
            'penerima_id' => $pb1_terima->id,
            'tanggal' => $today,
            'status' => 'dalam_perjalanan',
            'kode_verifikasi' => '456789',
            'waktu_dijemput' => $baseTime->copy()->setHour(13)->setMinute(5),
            'waktu_berangkat' => $baseTime->copy()->setHour(13)->setMinute(12),
            'waktu_tiba' => null,
            'catatan' => 'Perjalanan lancar melalui jalan utama.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p5->id,
            'diubah_oleh' => $admin->id,
            'status' => 'menunggu',
            'waktu' => $baseTime->copy()->setHour(8)->setMinute(0),
            'catatan' => 'Jadwal penjemputan dibuat.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p5->id,
            'diubah_oleh' => $driverUser1->id,
            'status' => 'dijemput',
            'waktu' => $baseTime->copy()->setHour(13)->setMinute(5),
            'catatan' => 'Anak telah dijemput.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p5->id,
            'diubah_oleh' => $driverUser1->id,
            'status' => 'dalam_perjalanan',
            'waktu' => $baseTime->copy()->setHour(13)->setMinute(12),
            'catatan' => 'Kendaraan berangkat menuju titik tujuan.',
        ]);

        // 6. Dalam Perjalanan
        $p6 = Penjemputan::create([
            'kode_perjalanan' => 'JA-20261103-006',
            'jadwal_id' => $jadwal3->id,
            'anak_id' => $anak3->id,
            'pengemudi_id' => $driver2->id,
            'penjemput_id' => $pb3_jemput->id,
            'penerima_id' => $pb3_terima->id,
            'tanggal' => $today,
            'status' => 'dalam_perjalanan',
            'kode_verifikasi' => '567890',
            'waktu_dijemput' => $baseTime->copy()->setHour(14)->setMinute(35),
            'waktu_berangkat' => $baseTime->copy()->setHour(14)->setMinute(40),
            'waktu_tiba' => null,
            'catatan' => 'Lalu lintas ramai lancar.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p6->id,
            'diubah_oleh' => $admin->id,
            'status' => 'menunggu',
            'waktu' => $baseTime->copy()->setHour(8)->setMinute(15),
            'catatan' => 'Jadwal penjemputan dibuat.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p6->id,
            'diubah_oleh' => $driverUser2->id,
            'status' => 'dijemput',
            'waktu' => $baseTime->copy()->setHour(14)->setMinute(35),
            'catatan' => 'Anak telah dijemput.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p6->id,
            'diubah_oleh' => $driverUser2->id,
            'status' => 'dalam_perjalanan',
            'waktu' => $baseTime->copy()->setHour(14)->setMinute(40),
            'catatan' => 'Berangkat menuju lokasi tujuan penerima.',
        ]);

        // 7. Tiba (belum serah terima)
        $p7 = Penjemputan::create([
            'kode_perjalanan' => 'JA-20261103-007',
            'jadwal_id' => $jadwal2->id,
            'anak_id' => $anak2->id,
            'pengemudi_id' => $driver1->id,
            'penjemput_id' => $pb2_jemput->id,
            'penerima_id' => $pb2_terima->id,
            'tanggal' => $today,
            'status' => 'tiba',
            'kode_verifikasi' => '678901',
            'waktu_dijemput' => $baseTime->copy()->setHour(12)->setMinute(5),
            'waktu_berangkat' => $baseTime->copy()->setHour(12)->setMinute(10),
            'waktu_tiba' => $baseTime->copy()->setHour(12)->setMinute(35),
            'catatan' => 'Telah sampai di depan rumah tujuan.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p7->id,
            'diubah_oleh' => $admin->id,
            'status' => 'menunggu',
            'waktu' => $baseTime->copy()->setHour(8)->setMinute(30),
            'catatan' => 'Jadwal dibuat.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p7->id,
            'diubah_oleh' => $driverUser1->id,
            'status' => 'dijemput',
            'waktu' => $baseTime->copy()->setHour(12)->setMinute(5),
            'catatan' => 'Anak dijemput di sekolah.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p7->id,
            'diubah_oleh' => $driverUser1->id,
            'status' => 'dalam_perjalanan',
            'waktu' => $baseTime->copy()->setHour(12)->setMinute(10),
            'catatan' => 'Berangkat ke tujuan.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p7->id,
            'diubah_oleh' => $driverUser1->id,
            'status' => 'tiba',
            'waktu' => $baseTime->copy()->setHour(12)->setMinute(35),
            'catatan' => 'Sampai di lokasi tujuan, menunggu konfirmasi serah terima penerima.',
        ]);

        // 8. Tiba dan Selesai (sudah ada serah_terima)
        $p8 = Penjemputan::create([
            'kode_perjalanan' => 'JA-20261103-008',
            'jadwal_id' => $jadwal4->id,
            'anak_id' => $anak4->id,
            'pengemudi_id' => $driver2->id,
            'penjemput_id' => $pb4_jemput->id,
            'penerima_id' => $pb4_terima->id,
            'tanggal' => $today,
            'status' => 'tiba',
            'kode_verifikasi' => '789012',
            'waktu_dijemput' => $baseTime->copy()->setHour(13)->setMinute(35),
            'waktu_berangkat' => $baseTime->copy()->setHour(13)->setMinute(40),
            'waktu_tiba' => $baseTime->copy()->setHour(14)->setMinute(5),
            'catatan' => 'Perjalanan selesai dengan selamat.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p8->id,
            'diubah_oleh' => $admin->id,
            'status' => 'menunggu',
            'waktu' => $baseTime->copy()->setHour(8)->setMinute(45),
            'catatan' => 'Jadwal dibuat.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p8->id,
            'diubah_oleh' => $driverUser2->id,
            'status' => 'dijemput',
            'waktu' => $baseTime->copy()->setHour(13)->setMinute(35),
            'catatan' => 'Anak dijemput.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p8->id,
            'diubah_oleh' => $driverUser2->id,
            'status' => 'dalam_perjalanan',
            'waktu' => $baseTime->copy()->setHour(13)->setMinute(40),
            'catatan' => 'Dalam perjalanan menuju rumah tujuan.',
        ]);
        RiwayatStatus::create([
            'penjemputan_id' => $p8->id,
            'diubah_oleh' => $driverUser2->id,
            'status' => 'tiba',
            'waktu' => $baseTime->copy()->setHour(14)->setMinute(5),
            'catatan' => 'Telah tiba di tujuan.',
        ]);

        SerahTerima::create([
            'penjemputan_id' => $p8->id,
            'penerima_id' => $pb4_terima->id,
            'kode_input' => '789012',
            'dikonfirmasi_at' => $baseTime->copy()->setHour(14)->setMinute(8),
            'bukti_path' => null,
        ]);

        // 7. Tiga (3) notifikasi contoh untuk orang tua 1
        Notifikasi::create([
            'user_id' => $ortu1->id,
            'penjemputan_id' => $p1->id,
            'judul' => 'Jadwal Penjemputan Kenzo',
            'isi' => 'Penjemputan anak Kenzo Pratama dijadwalkan hari ini pukul 13:00 WIB oleh Pak Joko Widodo.',
            'dibaca_at' => Carbon::now()->subHours(2),
        ]);

        Notifikasi::create([
            'user_id' => $ortu1->id,
            'penjemputan_id' => $p3->id,
            'judul' => 'Anak Telah Dijemput',
            'isi' => 'Alina Putri telah dijemput dari sekolah SD Negeri Mentari 01 oleh Pak Joko Widodo.',
            'dibaca_at' => Carbon::now()->subHour(),
        ]);

        Notifikasi::create([
            'user_id' => $ortu1->id,
            'penjemputan_id' => $p5->id,
            'judul' => 'Dalam Perjalanan Menuju Rumah Nenek',
            'isi' => 'Kenzo Pratama sedang dalam perjalanan menuju Rumah Nenek Maryam.',
            'dibaca_at' => null,
        ]);
    }
}
