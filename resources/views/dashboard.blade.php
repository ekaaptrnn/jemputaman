<x-layout title="Dashboard — JemputAman">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-brand-600 to-brand-800 rounded-2xl p-6 text-white shadow-md mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Selamat Datang, {{ auth()->user()->name }}!
                </h1>
                <p class="text-brand-100 text-sm mt-1">
                    Sistem Serah Terima Anak Antar-Jemput Resmi SD JemputAman Surakarta.
                </p>
            </div>
            <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-sm text-xs font-semibold text-white uppercase tracking-wider">
                <i class="fa-solid fa-shield-halved mr-1.5"></i>
                Peran: {{ str_replace('_', ' ', auth()->user()->role) }}
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
        <!-- Admin Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-brand-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-children"></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-500 block">Total Siswa</span>
                    <span class="text-2xl font-bold text-slate-900">{{ $stats['total_anak'] }}</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-van-shuttle"></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-500 block">Armada Driver</span>
                    <span class="text-2xl font-bold text-slate-900">{{ $stats['total_pengemudi'] }}</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-500 block">Jadwal Aktif</span>
                    <span class="text-2xl font-bold text-slate-900">{{ $stats['total_jadwal'] }}</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-route"></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-500 block">Jemputan Hari Ini</span>
                    <span class="text-2xl font-bold text-slate-900">{{ $stats['penjemputan_hari_ini'] }}</span>
                </div>
            </div>
        </div>

        <!-- Fase 1 Master Data Quick Action Bar -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm mb-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-3">
                Aksi Cepat Master Data (Fase 1 Selesai)
            </h2>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.anak.create') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold transition shadow-sm">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Tambah Siswa & Generate QR</span>
                </a>
                <a href="{{ route('admin.pengemudi.create') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold transition shadow-sm">
                    <i class="fa-solid fa-id-card text-xs"></i>
                    <span>Tambah Driver & Armada</span>
                </a>
                <a href="{{ route('admin.jadwal.create') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-semibold transition shadow-sm">
                    <i class="fa-solid fa-calendar-plus text-xs"></i>
                    <span>Atur Jadwal Rutin</span>
                </a>
                <a href="{{ route('admin.anak.index') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-sm font-semibold transition">
                    <i class="fa-solid fa-qrcode text-xs"></i>
                    <span>Cetak Kartu Siswa Ber-QR</span>
                </a>
            </div>
        </div>
    @endif

    @if(auth()->user()->isAdmin())
        <!-- Fase 3: Rekap perjalanan hari ini (admin) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-2xl border border-blue-200 shadow-sm">
                <span class="text-xs font-medium text-slate-500 block">Sedang Dalam Perjalanan</span>
                <span class="text-2xl font-bold text-blue-700">{{ $stats['dalam_perjalanan'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-sm">
                <span class="text-xs font-medium text-slate-500 block">Menunggu Konfirmasi Ortu</span>
                <span class="text-2xl font-bold text-amber-700">{{ $stats['menunggu_konfirmasi'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-sm">
                <span class="text-xs font-medium text-slate-500 block">Selesai Hari Ini</span>
                <span class="text-2xl font-bold text-emerald-700">{{ $stats['selesai'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-rose-200 shadow-sm">
                <span class="text-xs font-medium text-slate-500 block">Bermasalah</span>
                <span class="text-2xl font-bold text-rose-700">{{ $stats['bermasalah'] }}</span>
            </div>
        </div>
    @endif

    @if(auth()->user()->isGuru())
        <!-- Fase 3: Dashboard Guru — pemantauan gerbang piket -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm mb-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-3">
                <i class="fa-solid fa-door-open mr-1 text-emerald-600"></i> Pemantauan Gerbang Piket
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="rounded-xl bg-slate-50 p-3"><div class="text-2xl font-bold text-slate-900">{{ $stats['penjemputan_hari_ini'] }}</div><div class="text-xs text-slate-500">Total jemputan</div></div>
                <div class="rounded-xl bg-blue-50 p-3"><div class="text-2xl font-bold text-blue-700">{{ $stats['dalam_perjalanan'] }}</div><div class="text-xs text-slate-500">Di jalan</div></div>
                <div class="rounded-xl bg-amber-50 p-3"><div class="text-2xl font-bold text-amber-700">{{ $stats['menunggu_konfirmasi'] }}</div><div class="text-xs text-slate-500">Tunggu konfirmasi</div></div>
                <div class="rounded-xl bg-emerald-50 p-3"><div class="text-2xl font-bold text-emerald-700">{{ $stats['selesai'] }}</div><div class="text-xs text-slate-500">Selesai</div></div>
            </div>
            <p class="text-xs text-slate-400 mt-3">Klik <strong>Lacak</strong> pada perjalanan berstatus "Dalam Perjalanan" untuk melihat posisi armada di peta.</p>
        </div>
    @endif

    @if(auth()->user()->isPengemudi())
        <!-- Fase 3: Dashboard Driver — menu tugas harian -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm mb-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-3">
                <i class="fa-solid fa-route mr-1 text-amber-600"></i> Tugas Harian Anda
            </h2>
            @if($tugasBerikutnya)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-amber-50 border border-amber-100">
                    <div>
                        <div class="text-xs text-amber-700 font-semibold uppercase">Tugas berikutnya</div>
                        <div class="font-bold text-slate-900">{{ $tugasBerikutnya->anak->nama }} · Kelas {{ $tugasBerikutnya->anak->kelas }}</div>
                        <div class="text-xs text-slate-500">Jemput {{ \Illuminate\Support\Str::of($tugasBerikutnya->jadwal->jam_jemput)->substr(0, 5) }} · {{ $tugasBerikutnya->jadwal->titik_jemput }}</div>
                    </div>
                    <a href="{{ route('driver.penjemputan.show', $tugasBerikutnya) }}" class="inline-flex items-center justify-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold transition">
                        <i class="fa-solid fa-play mr-2 text-xs"></i>Buka Tugas
                    </a>
                </div>
            @else
                <p class="text-sm text-slate-500">Tidak ada tugas yang menunggu. Semua perjalanan hari ini sudah selesai atau belum dijadwalkan.</p>
            @endif
            <a href="{{ route('driver.penjemputan.index') }}" class="inline-block mt-3 text-sm font-semibold text-brand-600 hover:text-brand-700">Lihat semua jemputan hari ini &rarr;</a>
        </div>
    @endif

    @if(auth()->user()->isOrangTua())
        <!-- Fase 3: Dashboard Orang Tua — status anak & tombol live tracking -->
        <div class="mb-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-3">
                <i class="fa-solid fa-children mr-1 text-blue-600"></i> Anak Saya
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($anakSaya as $anak)
                    @php $jemputan = $penjemputanHariIni->firstWhere('anak_id', $anak->id); @endphp
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-bold text-slate-900">{{ $anak->nama }}</div>
                                <div class="text-xs text-slate-500">Kelas {{ $anak->kelas }}</div>
                            </div>
                            @if($jemputan)
                                @include('driver._badge', ['status' => $jemputan->status])
                            @else
                                <span class="inline-flex px-2.5 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-500">Tidak ada jemputan</span>
                            @endif
                        </div>

                        @if($jemputan)
                            <div class="mt-3 text-sm text-slate-600">
                                <i class="fa-solid fa-van-shuttle w-5 text-slate-400"></i>{{ $jemputan->pengemudi->user->name ?? '-' }} ({{ $jemputan->pengemudi->no_kendaraan ?? '-' }})
                            </div>
                            @if($jemputan->status === 'dalam_perjalanan')
                                <a href="{{ route('penjemputan.lacak', $jemputan) }}" class="mt-4 w-full inline-flex items-center justify-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold transition">
                                    <i class="fa-solid fa-location-crosshairs mr-2"></i>Lacak Sekarang
                                </a>
                            @elseif($jemputan->status === 'tiba')
                                <p class="mt-3 text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2">Armada sudah tiba. Silakan periksa foto bukti dan konfirmasi penerimaan Ananda.</p>
                                <a href="{{ route('konfirmasi.show', $jemputan) }}" class="mt-3 w-full inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition">
                                    <i class="fa-solid fa-circle-check mr-2"></i>Konfirmasi Penerimaan
                                </a>
                            @elseif(in_array($jemputan->status, ['selesai', 'bermasalah'], true))
                                <a href="{{ route('konfirmasi.show', $jemputan) }}" class="mt-3 inline-flex text-xs font-semibold text-brand-600 hover:text-brand-700">Lihat detail &rarr;</a>
                            @endif
                        @else
                            <p class="mt-3 text-xs text-slate-400">Belum ada perjalanan penjemputan hari ini.</p>
                        @endif
                    </div>
                @empty
                    <div class="sm:col-span-2 bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500">Belum ada data anak yang terhubung dengan akun Anda.</div>
                @endforelse
            </div>
        </div>
    @endif

    <!-- Tabel Monitoring Penjemputan Hari Ini -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-slate-900">Perjalanan Penjemputan Hari Ini</h3>
                <p class="text-xs text-slate-500">Data operasional yang terfilter sesuai hak akses akun Anda</p>
            </div>
            <span class="text-xs px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg font-medium">
                {{ \Carbon\Carbon::today()->isoFormat('dddd, D MMMM Y') }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Kode</th>
                        <th class="px-5 py-3.5">Siswa</th>
                        <th class="px-5 py-3.5">Armada Driver</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($penjemputanHariIni as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 font-mono font-medium text-slate-900 text-xs">
                                {{ $item->kode_perjalanan }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900">{{ $item->anak->nama ?? '-' }}</div>
                                <div class="text-xs text-slate-500">Kelas {{ $item->anak->kelas ?? '-' }} · QR: <span class="font-mono text-brand-600">{{ $item->anak->kode_qr ?? '-' }}</span></div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-800">{{ $item->pengemudi->user->name ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $item->pengemudi->jenis_kendaraan ?? '-' }} ({{ $item->pengemudi->no_kendaraan ?? '-' }})</div>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $badge = match($item->status) {
                                        'selesai' => 'bg-emerald-100 text-emerald-800',
                                        'dalam_perjalanan' => 'bg-blue-100 text-blue-800 animate-pulse',
                                        'tiba' => 'bg-amber-100 text-amber-800',
                                        'diserahkan' => 'bg-cyan-100 text-cyan-800',
                                        'menuju_sekolah' => 'bg-indigo-100 text-indigo-800',
                                        'bermasalah' => 'bg-rose-100 text-rose-800',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                    {{ strtoupper(str_replace('_', ' ', $item->status)) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if(auth()->user()->isPengemudi())
                                    <a href="{{ route('driver.penjemputan.show', $item) }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Buka</a>
                                @elseif($item->status === 'tiba' && auth()->user()->isOrangTua())
                                    <a href="{{ route('konfirmasi.show', $item) }}" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold">
                                        <i class="fa-solid fa-circle-check mr-1.5"></i>Konfirmasi
                                    </a>
                                @elseif($item->status === 'dalam_perjalanan')
                                    <a href="{{ route('penjemputan.lacak', $item) }}" class="inline-flex items-center px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-xs font-semibold">
                                        <i class="fa-solid fa-location-crosshairs mr-1.5"></i>Lacak
                                    </a>
                                @else
                                    <span class="text-xs text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-inbox text-3xl mb-2 block"></i>
                                Belum ada transaksi penjemputan untuk hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout>
