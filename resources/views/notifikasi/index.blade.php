<x-layout title="Notifikasi — JemputAman">
    <div class="flex items-center justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Notifikasi</h1>
            <p class="text-xs text-slate-500">Pemberitahuan status penjemputan untuk akun Anda</p>
        </div>

        @if($notifikasi->total() > 0)
            <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
                @csrf
                <button type="submit" class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 transition">
                    <i class="fa-solid fa-check-double mr-1.5"></i>Tandai semua dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="space-y-3">
        @forelse($notifikasi as $n)
            @php
                $belumDibaca = $n->dibaca_at === null;

                // Tujuan tombol "Buka": orang tua -> halaman konfirmasi, guru/admin -> halaman peta/lacak.
                $tujuan = null;
                if ($n->penjemputan) {
                    if (auth()->user()->isOrangTua()) {
                        $tujuan = route('konfirmasi.show', $n->penjemputan);
                    } elseif (auth()->user()->isGuru() || auth()->user()->isAdmin()) {
                        $tujuan = route('penjemputan.lacak', $n->penjemputan);
                    }
                }
            @endphp

            <div class="bg-white rounded-2xl border shadow-sm p-4 {{ $belumDibaca ? 'border-brand-200 bg-brand-50/40' : 'border-slate-200' }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            @if($belumDibaca)
                                <span class="inline-block w-2 h-2 rounded-full bg-brand-600" title="Belum dibaca"></span>
                            @endif
                            <h2 class="font-semibold text-slate-900 text-sm">{{ $n->judul }}</h2>
                        </div>
                        <p class="mt-1 text-sm text-slate-600">{{ $n->isi }}</p>
                        <div class="mt-2 text-xs text-slate-400">{{ $n->created_at->format('d-m-Y H:i') }}</div>
                    </div>

                    <div class="flex flex-col items-end gap-2 shrink-0">
                        @if($tujuan)
                            <a href="{{ $tujuan }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Buka</a>
                        @endif
                        @if($belumDibaca)
                            <form method="POST" action="{{ route('notifikasi.baca', $n) }}">
                                @csrf
                                <button type="submit" class="text-xs text-slate-500 hover:text-slate-800">Tandai dibaca</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500">
                <i class="fa-regular fa-bell-slash text-3xl mb-2 block text-slate-300"></i>
                Belum ada notifikasi.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $notifikasi->links() }}
    </div>
</x-layout>
