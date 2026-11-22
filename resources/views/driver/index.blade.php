<x-layout title="Jemputan Hari Ini — JemputAman">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Jemputan Hari Ini</h1>
        <p class="text-sm text-slate-500">{{ now()->translatedFormat('l, d F Y') }} · {{ $tugas->count() }} tugas</p>
    </div>

    <div class="space-y-3">
        @forelse($tugas as $p)
            <a href="{{ route('driver.penjemputan.show', $p) }}"
               class="block bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:border-brand-500 transition">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-bold text-slate-900">{{ $p->anak->nama }}</div>
                        <div class="text-xs text-slate-500">Kelas {{ $p->anak->kelas }} · {{ $p->kode_perjalanan }}</div>
                    </div>
                    @include('driver._badge', ['status' => $p->status])
                </div>
                <div class="mt-3 text-sm text-slate-600 space-y-1">
                    <div><i class="fa-regular fa-clock w-5 text-slate-400"></i>{{ \Illuminate\Support\Str::of($p->jadwal->jam_jemput)->substr(0, 5) }}</div>
                    <div><i class="fa-solid fa-school w-5 text-slate-400"></i>{{ $p->jadwal->titik_jemput }}</div>
                    <div><i class="fa-solid fa-house w-5 text-slate-400"></i>{{ $p->jadwal->titik_tujuan }}</div>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500">
                Tidak ada tugas jemputan untuk hari ini.
            </div>
        @endforelse
    </div>
</x-layout>
