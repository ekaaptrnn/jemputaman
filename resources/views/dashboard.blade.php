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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-slate-400">
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
