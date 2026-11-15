@php
    $peta = [
        'dijadwalkan'      => ['Dijadwalkan', 'bg-slate-100 text-slate-700'],
        'menuju_sekolah'   => ['Menuju Sekolah', 'bg-amber-100 text-amber-800'],
        'diserahkan'       => ['Diserahkan', 'bg-sky-100 text-sky-800'],
        'dalam_perjalanan' => ['Dalam Perjalanan', 'bg-blue-100 text-blue-800'],
        'tiba'             => ['Tiba', 'bg-violet-100 text-violet-800'],
        'selesai'          => ['Selesai', 'bg-emerald-100 text-emerald-800'],
        'bermasalah'       => ['Bermasalah', 'bg-rose-100 text-rose-800'],
    ];
    [$teks, $kelas] = $peta[$status] ?? [$status, 'bg-slate-100 text-slate-700'];
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold {{ $kelas }}">{{ $teks }}</span>
