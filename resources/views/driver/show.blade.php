<x-layout title="Detail Jemputan — JemputAman">
    <a href="{{ route('driver.penjemputan.index') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-slate-800 mb-4">
        <i class="fa-solid fa-arrow-left mr-2 text-xs"></i>Kembali ke daftar tugas
    </a>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-900">{{ $p->anak->nama }}</h1>
                <div class="text-xs text-slate-500">Kelas {{ $p->anak->kelas }} · {{ $p->kode_perjalanan }}</div>
            </div>
            @include('driver._badge', ['status' => $p->status])
        </div>
        <div class="mt-3 text-sm text-slate-600 space-y-1">
            <div><i class="fa-regular fa-clock w-5 text-slate-400"></i>Jemput {{ \Illuminate\Support\Str::of($p->jadwal->jam_jemput)->substr(0, 5) }}</div>
            <div><i class="fa-solid fa-school w-5 text-slate-400"></i>{{ $p->jadwal->titik_jemput }}</div>
            <div><i class="fa-solid fa-house w-5 text-slate-400"></i>{{ $p->jadwal->titik_tujuan }}</div>
        </div>
    </div>

    {{-- Panel aksi sesuai status --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-4">
        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Langkah Berikutnya</h2>

        @switch($p->status)
            @case('dijadwalkan')
                <form method="POST" action="{{ route('driver.penjemputan.berangkat', $p) }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold transition">
                        <i class="fa-solid fa-van-shuttle mr-2"></i>Berangkat ke Sekolah
                    </button>
                </form>
                @break

            @case('menuju_sekolah')
                <form method="POST" action="{{ route('driver.penjemputan.serah-terima', $p) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="kode_qr" class="block text-sm font-medium text-slate-700 mb-1">Kode QR Siswa</label>
                        <input type="text" id="kode_qr" name="kode_qr" value="{{ old('kode_qr') }}" autocomplete="off" autofocus
                               placeholder="Scan kartu siswa atau ketik kode"
                               class="w-full px-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                        <button type="button" id="btn-scan" class="mt-2 inline-flex items-center px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold">
                            <i class="fa-solid fa-qrcode mr-2"></i>Scan dengan Kamera
                        </button>
                        <div id="qr-reader" class="hidden mt-3 max-w-sm"></div>
                    </div>
                    <div>
                        <label for="foto" class="block text-sm font-medium text-slate-700 mb-1">Foto Serah Terima di Gerbang Sekolah</label>
                        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png" capture="environment"
                               class="w-full text-sm text-slate-600 file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700">
                        <p class="text-xs text-slate-400 mt-1">JPG atau PNG, maksimal 5 MB.</p>
                    </div>
                    <button type="submit" class="w-full px-4 py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold transition">
                        <i class="fa-solid fa-child-reaching mr-2"></i>Konfirmasi Serah Terima
                    </button>
                </form>

                <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
                <script>
                    (function () {
                        const tombol = document.getElementById('btn-scan');
                        const kotak = document.getElementById('qr-reader');
                        const isian = document.getElementById('kode_qr');
                        let pemindai = null;

                        async function berhenti() {
                            if (!pemindai) return;
                            try { await pemindai.stop(); pemindai.clear(); } catch (e) {}
                            pemindai = null;
                            kotak.classList.add('hidden');
                            tombol.innerHTML = '<i class="fa-solid fa-qrcode mr-2"></i>Scan dengan Kamera';
                        }

                        tombol.addEventListener('click', async function () {
                            if (pemindai) { await berhenti(); return; }
                            if (typeof Html5Qrcode === 'undefined') {
                                alert('Library scanner gagal dimuat. Ketik kode QR secara manual.');
                                return;
                            }
                            kotak.classList.remove('hidden');
                            pemindai = new Html5Qrcode('qr-reader');
                            try {
                                await pemindai.start(
                                    { facingMode: 'environment' },
                                    { fps: 10, qrbox: { width: 220, height: 220 } },
                                    function (teks) { isian.value = teks.trim(); berhenti(); }
                                );
                                tombol.textContent = 'Tutup Kamera';
                            } catch (e) {
                                alert('Kamera tidak bisa dibuka. Kamera butuh HTTPS atau localhost dan izin kamera. Ketik kode QR secara manual.');
                                await berhenti();
                            }
                        });
                    })();
                </script>
                @break

            @case('diserahkan')
                <form method="POST" action="{{ route('driver.penjemputan.mulai', $p) }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold transition">
                        <i class="fa-solid fa-route mr-2"></i>Mulai Perjalanan ke Rumah
                    </button>
                </form>
                @break

            @case('dalam_perjalanan')
                <form method="POST" action="{{ route('driver.penjemputan.tiba', $p) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="foto" class="block text-sm font-medium text-slate-700 mb-1">Foto Kedatangan di Rumah Siswa</label>
                        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png" capture="environment"
                               class="w-full text-sm text-slate-600 file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700">
                        <p class="text-xs text-slate-400 mt-1">JPG atau PNG, maksimal 5 MB.</p>
                    </div>
                    <button type="submit" class="w-full px-4 py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold transition">
                        <i class="fa-solid fa-house-circle-check mr-2"></i>Konfirmasi Tiba
                    </button>
                </form>
                @break

            @case('tiba')
                <p class="text-sm text-slate-600">Armada sudah tiba. Menunggu konfirmasi penerimaan dari orang tua.</p>
                @break

            @default
                <p class="text-sm text-slate-600">Perjalanan ini sudah ditutup. Tidak ada aksi yang tersedia.</p>
        @endswitch
    </div>

    {{-- Foto bukti (disajikan lewat route berotorisasi, bukan URL storage publik) --}}
    @if($p->foto_serah_sekolah || $p->foto_tiba)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-4">
            <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Foto Bukti</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if($p->foto_serah_sekolah)
                    <figure>
                        <img src="{{ route('penjemputan.foto', [$p, 'serah']) }}" alt="Foto serah terima di sekolah" class="rounded-xl border border-slate-200 w-full object-cover">
                        <figcaption class="text-xs text-slate-500 mt-1">Serah terima di sekolah</figcaption>
                    </figure>
                @endif
                @if($p->foto_tiba)
                    <figure>
                        <img src="{{ route('penjemputan.foto', [$p, 'tiba']) }}" alt="Foto kedatangan di rumah" class="rounded-xl border border-slate-200 w-full object-cover">
                        <figcaption class="text-xs text-slate-500 mt-1">Kedatangan di rumah</figcaption>
                    </figure>
                @endif
            </div>
        </div>
    @endif

    {{-- Riwayat status (audit trail) --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Riwayat Status</h2>
        <ol class="space-y-3">
            @foreach($p->riwayatStatus as $r)
                <li class="flex items-start gap-3">
                    <div class="mt-0.5">@include('driver._badge', ['status' => $r->status])</div>
                    <div class="text-sm text-slate-600">
                        <div>{{ $r->catatan }}</div>
                        <div class="text-xs text-slate-400">{{ $r->waktu->format('H:i') }} · {{ $r->pengubah->name ?? '-' }}</div>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</x-layout>
