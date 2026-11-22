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
                {{-- Fase 3: pengirim koordinat GPS (aktif hanya saat status dalam_perjalanan) --}}
                <div class="mb-5 p-4 rounded-xl bg-blue-50 border border-blue-100">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm font-semibold text-blue-900"><i class="fa-solid fa-satellite-dish mr-2"></i>Pelacakan GPS</div>
                        <span id="gps-status" class="text-xs font-semibold text-slate-500">Menyiapkan…</span>
                    </div>
                    <p id="gps-detail" class="text-xs text-slate-500 mt-1">Biarkan halaman ini terbuka agar orang tua bisa memantau posisi armada.</p>
                    <button type="button" id="btn-simulasi" class="mt-3 inline-flex items-center px-3 py-2 bg-slate-700 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold">
                        <i class="fa-solid fa-flask mr-2"></i>Mode Simulasi (untuk demo)
                    </button>
                </div>

                <script>
                    (function () {
                        const URL_KIRIM = @json(route('penjemputan.lokasi.store', $p));
                        const CSRF = @json(csrf_token());
                        const JEDA_KIRIM_MS = 6000; // sesuai rancangan: kirim tiap 5-10 detik
                        const elStatus = document.getElementById('gps-status');
                        const elDetail = document.getElementById('gps-detail');
                        const tombolSim = document.getElementById('btn-simulasi');

                        let terakhirKirim = 0;
                        let watchId = null;
                        let timerSimulasi = null;

                        function tampil(status, detail, warna) {
                            elStatus.textContent = status;
                            elStatus.className = 'text-xs font-semibold ' + (warna || 'text-slate-500');
                            if (detail) elDetail.textContent = detail;
                        }

                        async function kirim(lat, lng) {
                            try {
                                const res = await fetch(URL_KIRIM, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': CSRF
                                    },
                                    credentials: 'same-origin',
                                    body: JSON.stringify({ latitude: lat, longitude: lng })
                                });
                                if (res.status === 429) { tampil('Terlalu sering', 'Server membatasi frekuensi kirim. Menunggu giliran berikutnya.', 'text-amber-600'); return; }
                                if (!res.ok) { const e = await res.json().catch(() => ({})); tampil('Gagal kirim', e.pesan || ('HTTP ' + res.status), 'text-rose-600'); return; }
                                tampil('Terkirim ' + new Date().toLocaleTimeString('id-ID'), lat.toFixed(5) + ', ' + lng.toFixed(5), 'text-emerald-600');
                            } catch (e) {
                                tampil('Offline', 'Koneksi terputus, mencoba lagi pada titik berikutnya.', 'text-rose-600');
                            }
                        }

                        // --- GPS asli: navigator.geolocation.watchPosition (butuh HTTPS atau localhost) ---
                        function mulaiGpsAsli() {
                            if (!('geolocation' in navigator)) {
                                tampil('GPS tidak didukung', 'Browser ini tidak mendukung Geolocation. Pakai Mode Simulasi.', 'text-rose-600');
                                return;
                            }
                            if (!window.isSecureContext) {
                                tampil('Butuh HTTPS', 'Geolocation ditolak di http://IP-laptop. Pakai HTTPS/tunnel, localhost, atau Mode Simulasi.', 'text-amber-600');
                                return;
                            }
                            watchId = navigator.geolocation.watchPosition(
                                function (pos) {
                                    const sekarang = Date.now();
                                    if (sekarang - terakhirKirim < JEDA_KIRIM_MS) return;
                                    terakhirKirim = sekarang;
                                    kirim(pos.coords.latitude, pos.coords.longitude);
                                },
                                function (err) {
                                    const pesan = { 1: 'Izin lokasi ditolak. Aktifkan izin lokasi di browser.', 2: 'Posisi tidak tersedia.', 3: 'Waktu menunggu GPS habis.' };
                                    tampil('GPS error', pesan[err.code] || err.message, 'text-rose-600');
                                },
                                { enableHighAccuracy: true, maximumAge: 2000, timeout: 15000 }
                            );
                            tampil('Mencari sinyal…', null, 'text-blue-600');
                        }

                        // --- Mode simulasi: rute dummy di Surakarta, untuk demo tanpa HTTPS ---
                        const RUTE = [
                            [-7.5582, 110.8205], [-7.5590, 110.8216], [-7.5601, 110.8228], [-7.5614, 110.8240],
                            [-7.5628, 110.8252], [-7.5640, 110.8266], [-7.5652, 110.8281], [-7.5663, 110.8297],
                            [-7.5670, 110.8320], [-7.5679, 110.8338]
                        ];
                        function toggleSimulasi() {
                            if (timerSimulasi) {
                                clearInterval(timerSimulasi); timerSimulasi = null;
                                tombolSim.innerHTML = '<i class="fa-solid fa-flask mr-2"></i>Mode Simulasi (untuk demo)';
                                tampil('Simulasi berhenti', null);
                                return;
                            }
                            if (watchId !== null) { navigator.geolocation.clearWatch(watchId); watchId = null; }
                            let i = 0;
                            const langkah = function () { kirim(RUTE[i][0], RUTE[i][1]); i = (i + 1) % RUTE.length; };
                            langkah();
                            timerSimulasi = setInterval(langkah, JEDA_KIRIM_MS);
                            tombolSim.innerHTML = '<i class="fa-solid fa-stop mr-2"></i>Hentikan Simulasi';
                        }

                        tombolSim.addEventListener('click', toggleSimulasi);
                        mulaiGpsAsli();
                    })();
                </script>

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
