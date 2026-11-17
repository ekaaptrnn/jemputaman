# Fase 3 — Live GPS Tracking: Catatan Teknis & Cara Demo

## Endpoint
| Method | URL | Siapa | Fungsi |
|---|---|---|---|
| POST | `/penjemputan/{kode}/lokasi` | pengemudi pemilik, status `dalam_perjalanan` | simpan `latitude`, `longitude` (throttle 20/menit) |
| GET | `/penjemputan/{kode}/lokasi-terkini` | semua peran yang lolos `visibleTo` | JSON koordinat terakhir (`?jejak=1` + riwayat titik) |
| GET | `/penjemputan/{kode}/lacak` | orang tua, guru, admin | halaman peta Leaflet (polling 5 detik) |

`{kode}` = `kode_perjalanan`, contoh `JA-20261117-002`.

## Cara uji tanpa HP / tanpa HTTPS
1. Login sebagai pengemudi, buka tugas berstatus **dalam_perjalanan**, klik **Mode Simulasi (untuk demo)**.
   Koordinat dummy rute Surakarta dikirim tiap 6 detik.
2. Di browser/laptop lain login sebagai orang tua pemilik anak (atau guru), klik **Lacak** → marker bergerak.

## Alternatif lewat tinker
```php
php artisan tinker
>>> $p = App\Models\Penjemputan::where('kode_perjalanan','JA-20261117-002')->first();
>>> App\Models\LokasiDriver::create(['penjemputan_id'=>$p->id,'latitude'=>-7.5650,'longitude'=>110.8270,'dicatat_pada'=>now()]);
```
(ulang dengan koordinat berbeda; peta akan menggeser marker dalam ≤ 5 detik)

## GPS asli di HP
`navigator.geolocation` hanya jalan di HTTPS atau `localhost`. Untuk demo di HP gunakan tunnel HTTPS
(mis. `ngrok http 8000` atau `cloudflared tunnel --url http://localhost:8000`), lalu buka URL https dari tunnel.
