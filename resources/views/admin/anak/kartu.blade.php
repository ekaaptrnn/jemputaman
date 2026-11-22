<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Penjemputan — {{ $anak->nama }} ({{ $anak->kode_qr }})</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; margin: 0; padding: 20px; }
            .card-container { box-shadow: none !important; border: 1px solid #cbd5e1 !important; page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-10 px-4 flex flex-col items-center justify-center font-sans">

    <!-- Action Bar (Hidden on print) -->
    <div class="w-full max-w-sm mb-6 flex justify-between items-center no-print">
        <a href="{{ route('admin.anak.index') }}" class="inline-flex items-center space-x-1.5 text-sm text-slate-600 hover:text-slate-900 transition font-medium">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Kembali</span>
        </a>
        <button onclick="window.print()" class="inline-flex items-center space-x-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
            <i class="fa-solid fa-print"></i>
            <span>Cetak Kartu</span>
        </button>
    </div>

    <!-- Printable Lanyard / ID Card -->
    <div class="card-container bg-white w-full max-w-sm rounded-3xl shadow-xl border border-slate-200 overflow-hidden relative">
        <!-- Card Lanyard Hole Visual Indicator -->
        <div class="w-full py-2.5 flex justify-center bg-slate-50 border-b border-slate-100">
            <div class="w-12 h-2.5 bg-slate-300 rounded-full"></div>
        </div>

        <!-- Header Sekolah -->
        <div class="bg-gradient-to-r from-blue-700 to-indigo-800 p-5 text-white text-center relative">
            <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-lg">
                <i class="fa-solid fa-van-shuttle"></i>
            </div>
            <h2 class="text-xs font-semibold tracking-wider uppercase text-blue-200">Kartu Resmi Antar-Jemput</h2>
            <h1 class="text-lg font-bold tracking-tight">{{ $anak->sekolah->nama ?? 'SD JEMPUTAMAN SURAKARTA' }}</h1>
            <p class="text-[10px] text-blue-200 mt-0.5">{{ $anak->sekolah->alamat ?? 'Kota Surakarta' }}</p>
        </div>

        <!-- Card Body -->
        <div class="p-6 text-center space-y-4">
            <!-- Student Avatar -->
            <div class="w-20 h-20 mx-auto rounded-full bg-slate-100 border-4 border-blue-500/20 flex items-center justify-center text-slate-400 text-3xl shadow-inner">
                <i class="fa-solid fa-user-graduate"></i>
            </div>

            <!-- Student Name & Class -->
            <div>
                <h3 class="text-xl font-extrabold text-slate-900 leading-tight">{{ $anak->nama }}</h3>
                <span class="inline-block mt-1 px-3 py-0.5 bg-blue-50 text-blue-700 text-xs font-bold rounded-full">
                    Kelas {{ $anak->kelas }}
                </span>
            </div>

            <!-- QR Code Section -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 inline-block shadow-sm">
                <!-- QR Code via Google Charts or QRServer open API with fast CDN -->
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($anak->kode_qr) }}&format=svg" 
                    alt="Kode QR Siswa {{ $anak->kode_qr }}" 
                    class="w-40 h-40 mx-auto rounded-lg">
                <div class="mt-2 font-mono text-xs font-bold text-slate-700 tracking-wider">
                    {{ $anak->kode_qr }}
                </div>
            </div>

            <!-- Wali Murid Details -->
            <div class="pt-3 border-t border-slate-100 text-left text-xs space-y-1 bg-slate-50/50 p-3 rounded-xl">
                <div class="flex justify-between">
                    <span class="text-slate-500">Wali Murid:</span>
                    <span class="font-semibold text-slate-800">{{ $anak->orangTua->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Kontak Darurat:</span>
                    <span class="font-semibold text-slate-800">{{ $anak->orangTua->no_hp ?? '-' }}</span>
                </div>
            </div>

            <p class="text-[10px] text-slate-400 italic">
                *Wajib dikalungkan / dibawa saat jam kepulangan sekolah untuk dipindai oleh driver armada.
            </p>
        </div>
    </div>

</body>
</html>
