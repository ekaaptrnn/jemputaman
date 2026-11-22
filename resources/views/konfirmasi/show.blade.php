<x-layout title="Konfirmasi Penerimaan — JemputAman">
    <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-slate-800 mb-4">
        <i class="fa-solid fa-arrow-left mr-2 text-xs"></i>Kembali ke dashboard
    </a>

    {{-- Ringkasan perjalanan --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-900">{{ $p->anak->nama }}</h1>
                <div class="text-xs text-slate-500">Kelas {{ $p->anak->kelas }} · {{ $p->kode_perjalanan }}</div>
            </div>
            @include('driver._badge', ['status' => $p->status])
        </div>
        <div class="mt-3 text-sm text-slate-600 space-y-1">
            <div><i class="fa-solid fa-van-shuttle w-5 text-slate-400"></i>{{ $p->pengemudi->user->name ?? '-' }} ({{ $p->pengemudi->no_kendaraan ?? '-' }})</div>
            <div><i class="fa-solid fa-school w-5 text-slate-400"></i>Dijemput dari {{ $p->jadwal->titik_jemput }}</div>
            <div><i class="fa-solid fa-house w-5 text-slate-400"></i>Tujuan: {{ $p->jadwal->titik_tujuan }}</div>
            @if($p->waktu_tiba)
                <div><i class="fa-regular fa-clock w-5 text-slate-400"></i>Armada tiba pukul {{ $p->waktu_tiba->format('H:i') }}</div>
            @endif
        </div>
    </div>

    {{-- Foto bukti (disajikan lewat route berotorisasi penjemputan.foto, bukan URL storage publik) --}}
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

    {{-- Panel aksi sesuai status --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-4">
        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Konfirmasi Penerimaan</h2>

        @switch($p->status)
            @case('tiba')
                <p class="text-sm text-slate-600 mb-4">Armada sudah tiba. Periksa foto bukti di atas, lalu konfirmasi jika Ananda sudah Anda terima dengan aman.</p>

                <form method="POST" action="{{ route('konfirmasi.terima', $p) }}" onsubmit="return confirm('Konfirmasi bahwa anak sudah Anda terima dengan aman?');">
                    @csrf
                    <button type="submit" class="w-full px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition">
                        <i class="fa-solid fa-circle-check mr-2"></i>Anak Telah Diterima
                    </button>
                </form>

                <div class="my-5 flex items-center gap-3 text-xs text-slate-400">
                    <span class="flex-1 border-t border-slate-200"></span>atau<span class="flex-1 border-t border-slate-200"></span>
                </div>

                <form method="POST" action="{{ route('konfirmasi.laporkan', $p) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label for="catatan" class="block text-sm font-medium text-slate-700 mb-1">Ada kendala? Ceritakan singkat (wajib diisi)</label>
                        <textarea id="catatan" name="catatan" rows="3" required minlength="5" maxlength="1000"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-rose-500 focus:ring-rose-500"
                            placeholder="Contoh: anak belum diterima, alamat tujuan tidak sesuai, dll.">{{ old('catatan') }}</textarea>
                    </div>
                    <button type="submit" class="w-full px-4 py-3 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-sm font-semibold transition">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i>Laporkan Kendala
                    </button>
                </form>
                @break

            @case('selesai')
                <p class="text-sm text-emerald-700 bg-emerald-50 rounded-xl px-4 py-3">
                    <i class="fa-solid fa-circle-check mr-1"></i>
                    Penerimaan sudah dikonfirmasi{{ $p->waktu_konfirmasi_ortu ? ' pada '.$p->waktu_konfirmasi_ortu->format('d-m-Y H:i') : '' }}.
                </p>
                @break

            @case('bermasalah')
                <p class="text-sm text-rose-700 bg-rose-50 rounded-xl px-4 py-3">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    Kendala sudah dilaporkan ke pihak sekolah. Catatan: {{ $p->catatan }}
                </p>
                @break

            @case('dalam_perjalanan')
                <p class="text-sm text-slate-600 mb-3">Armada masih dalam perjalanan. Konfirmasi tersedia setelah armada tiba.</p>
                <a href="{{ route('penjemputan.lacak', $p) }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold transition">
                    <i class="fa-solid fa-location-crosshairs mr-2"></i>Lacak Sekarang
                </a>
                @break

            @default
                <p class="text-sm text-slate-500">Konfirmasi tersedia setelah armada tiba di rumah.</p>
        @endswitch
    </div>

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
