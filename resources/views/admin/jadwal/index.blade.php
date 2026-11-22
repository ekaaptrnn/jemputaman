<x-layout title="Jadwal Penjemputan Rutin — JemputAman">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pola Jadwal Penjemputan Rutin</h1>
            <p class="text-sm text-slate-500">Atur penugasan armada driver dan rute penjemputan per anak.</p>
        </div>
        <a href="{{ route('admin.jadwal.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-semibold transition shadow-sm shadow-purple-500/20">
            <i class="fa-solid fa-calendar-plus text-xs"></i>
            <span>Tambah Jadwal Rutin</span>
        </a>
    </div>

    <!-- Schedules Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Siswa</th>
                        <th class="px-5 py-3.5">Armada Driver</th>
                        <th class="px-5 py-3.5">Hari & Jam</th>
                        <th class="px-5 py-3.5">Titik Jemput & Tujuan</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($jadwalList as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $item->anak->nama ?? '-' }}</div>
                                <div class="text-xs text-slate-500">Kelas {{ $item->anak->kelas ?? '-' }} · Ortu: {{ $item->anak->orangTua->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-800">{{ $item->pengemudi->user->name ?? '-' }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $item->pengemudi->no_kendaraan ?? '-' }} ({{ $item->pengemudi->jenis_kendaraan ?? '-' }})</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900">{{ $item->hari }}</div>
                                <div class="text-xs text-purple-700 font-mono font-bold"><i class="fa-regular fa-clock mr-1"></i>{{ $item->jam_jemput }} WIB</div>
                            </td>
                            <td class="px-5 py-4 text-xs">
                                <div><span class="text-slate-400">Dari:</span> <span class="font-medium text-slate-700">{{ $item->titik_jemput }}</span></div>
                                <div class="mt-0.5"><span class="text-slate-400">Ke:</span> <span class="font-medium text-slate-700">{{ $item->titik_tujuan }}</span></div>
                            </td>
                            <td class="px-5 py-4">
                                @if($item->aktif)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                        Non-Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center space-x-2">
                                    <a href="{{ route('admin.jadwal.edit', $item) }}"
                                        title="Edit Jadwal"
                                        class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900 transition">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </a>

                                    <form action="{{ route('admin.jadwal.destroy', $item) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Jadwal"
                                            class="p-2 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 transition">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-calendar-xmark text-3xl mb-2 block"></i>
                                Belum ada pola jadwal penjemputan rutin.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jadwalList->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $jadwalList->links() }}
            </div>
        @endif
    </div>
</x-layout>
