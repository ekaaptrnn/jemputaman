<x-layout title="Lacak Armada — JemputAman">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
    <style>
        #peta { height: 60vh; min-height: 340px; border-radius: 1rem; z-index: 0; }
        .marker-armada {
            background: #2563eb; color: #fff; border: 3px solid #fff; border-radius: 9999px;
            box-shadow: 0 2px 8px rgba(0,0,0,.35); display: flex; align-items: center; justify-content: center;
        }
    </style>

    <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-slate-800 mb-4">
        <i class="fa-solid fa-arrow-left mr-2 text-xs"></i>Kembali ke dashboard
    </a>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Lacak Armada — {{ $p->anak->nama }}</h1>
                <div class="text-xs text-slate-500">Kelas {{ $p->anak->kelas }} · {{ $p->kode_perjalanan }}</div>
            </div>
            <span id="badge-status">@include('driver._badge', ['status' => $p->status])</span>
        </div>
        <div class="mt-3 text-sm text-slate-600 space-y-1">
            <div><i class="fa-solid fa-van-shuttle w-5 text-slate-400"></i>{{ $p->pengemudi->user->name ?? '-' }} · {{ $p->pengemudi->jenis_kendaraan ?? '-' }} ({{ $p->pengemudi->no_kendaraan ?? '-' }})</div>
            <div><i class="fa-solid fa-house w-5 text-slate-400"></i>Tujuan: {{ $p->jadwal->titik_tujuan }}</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-3 mb-4">
        <div id="info-peta" class="px-2 py-2 text-sm text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-circle-notch fa-spin text-slate-400"></i><span>Memuat posisi armada…</span>
        </div>
        <div id="peta"></div>
        <p class="text-xs text-slate-400 mt-2 px-2">Posisi diperbarui otomatis tiap 5 detik. Peta © kontributor OpenStreetMap.</p>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    <script>
        (function () {
            const URL_TERKINI = @json(route('penjemputan.lokasi.terkini', $p));
            const INTERVAL_MS = 5000;
            const PUSAT_DEFAULT = [-7.5666, 110.8167]; // Surakarta, dipakai jika belum ada titik GPS

            const info = document.getElementById('info-peta');
            const badge = document.getElementById('badge-status');
            const peta = L.map('peta').setView(PUSAT_DEFAULT, 14);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(peta);

            const ikon = L.divIcon({
                className: '',
                html: '<div class="marker-armada" style="width:38px;height:38px"><i class="fa-solid fa-van-shuttle"></i></div>',
                iconSize: [38, 38],
                iconAnchor: [19, 19]
            });

            const jalur = L.polyline([], { color: '#2563eb', weight: 4, opacity: 0.6 }).addTo(peta);
            let marker = null;
            let pertama = true;
            let timer = null;

            function setInfo(html, ikonKelas) {
                info.innerHTML = '<i class="fa-solid ' + ikonKelas + ' text-slate-400"></i><span>' + html + '</span>';
            }

            // Animasi geser marker dari posisi lama ke posisi baru (~1 detik) supaya terlihat bergerak mulus.
            function geserMarker(tujuan) {
                const awal = marker.getLatLng();
                const akhir = L.latLng(tujuan[0], tujuan[1]);
                if (awal.equals(akhir)) return;
                const durasi = 1000, mulai = performance.now();
                (function langkah(sekarang) {
                    const t = Math.min(1, (sekarang - mulai) / durasi);
                    marker.setLatLng([
                        awal.lat + (akhir.lat - awal.lat) * t,
                        awal.lng + (akhir.lng - awal.lng) * t
                    ]);
                    if (t < 1) requestAnimationFrame(langkah);
                })(mulai);
            }

            async function muat() {
                try {
                    const res = await fetch(URL_TERKINI + (pertama ? '?jejak=1' : ''), {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const data = await res.json();

                    if (pertama && data.jejak && data.jejak.length) {
                        jalur.setLatLngs(data.jejak);
                    }

                    if (data.lokasi) {
                        const titik = [data.lokasi.latitude, data.lokasi.longitude];
                        if (!marker) {
                            marker = L.marker(titik, { icon: ikon }).addTo(peta);
                            peta.setView(titik, 16);
                        } else {
                            geserMarker(titik);
                            peta.panTo(titik, { animate: true, duration: 1 });
                        }
                        if (!pertama) jalur.addLatLng(titik);

                        const lalu = data.lokasi.detik_lalu;
                        setInfo('Posisi terakhir diterima ' + (lalu < 60 ? lalu + ' detik' : Math.round(lalu / 60) + ' menit') + ' yang lalu.', 'fa-location-dot');
                    } else if (data.aktif) {
                        setInfo('Armada dalam perjalanan, menunggu sinyal GPS pertama dari pengemudi…', 'fa-satellite-dish');
                    } else {
                        setInfo('Belum ada data lokasi untuk perjalanan ini.', 'fa-circle-info');
                    }

                    badge.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700">' + data.status_label + '</span>';

                    // Armada sudah sampai / perjalanan ditutup: hentikan polling.
                    // Jika perjalanan belum dimulai, polling tetap jalan supaya peta hidup begitu driver berangkat.
                    if (['tiba', 'selesai', 'bermasalah'].includes(data.status)) {
                        clearInterval(timer);
                        setInfo('Armada sudah tiba di tujuan. Pelacakan langsung dihentikan.', 'fa-flag-checkered');
                    } else if (!data.aktif && !data.lokasi) {
                        setInfo('Perjalanan belum dimulai. Peta aktif otomatis setelah armada meninggalkan sekolah.', 'fa-hourglass-half');
                    }
                    pertama = false;
                } catch (e) {
                    setInfo('Gagal memuat posisi (' + e.message + '). Mencoba lagi…', 'fa-triangle-exclamation');
                }
            }

            muat();
            timer = setInterval(muat, INTERVAL_MS);
        })();
    </script>
</x-layout>
