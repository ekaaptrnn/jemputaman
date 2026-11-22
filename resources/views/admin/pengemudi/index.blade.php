<x-layout title="Driver & Armada Sekolah — JemputAman">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Driver & Armada Sekolah</h1>
            <p class="text-sm text-slate-500">Kelola staf pengemudi resmi sekolah dan kendaraan antar-jemput aktif.</p>
        </div>
        <a href="{{ route('admin.pengemudi.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold transition shadow-sm shadow-amber-500/20">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Tambah Driver & Armada</span>
        </a>
    </div>

    <!-- Drivers Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Nama Driver</th>
                        <th class="px-5 py-3.5">Akun Login</th>
                        <th class="px-5 py-3.5">No. Plat & Kendaraan</th>
                        <th class="px-5 py-3.5">Status Armada</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pengemudiList as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $item->user->name ?? '-' }}</div>
                                <div class="text-xs text-slate-500">No. HP: {{ $item->user->no_hp ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 font-mono text-xs text-slate-600">
                                {{ $item->user->email ?? '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-mono font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded text-xs">
                                    {{ $item->no_kendaraan }}
                                </span>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $item->jenis_kendaraan }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @if($item->aktif)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-circle text-[8px] mr-1.5 text-emerald-500"></i> Aktif Beroperasi
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                        <i class="fa-solid fa-circle text-[8px] mr-1.5 text-slate-400"></i> Non-Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center space-x-2">
                                    <a href="{{ route('admin.pengemudi.edit', $item) }}"
                                        title="Edit Driver"
                                        class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900 transition">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </a>

                                    <form action="{{ route('admin.pengemudi.destroy', $item) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data driver {{ $item->user->name ?? '' }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Driver"
                                            class="p-2 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 transition">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-id-card text-3xl mb-2 block"></i>
                                Belum ada data driver dan armada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pengemudiList->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $pengemudiList->links() }}
            </div>
        @endif
    </div>
</x-layout>
