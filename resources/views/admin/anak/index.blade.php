<x-layout title="Data Siswa & Kode QR — JemputAman">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Data Siswa & Kode QR</h1>
            <p class="text-sm text-slate-500">Kelola identitas siswa dan kartu QR unik untuk validasi serah terima di sekolah.</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.anak.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold transition shadow-sm shadow-brand-500/20">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Tambah Siswa Baru</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6">
        <form method="GET" action="{{ route('admin.anak.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa, kelas, atau kode QR..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.anak.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition flex items-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Student Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Nama Siswa</th>
                        <th class="px-5 py-3.5">Kelas</th>
                        <th class="px-5 py-3.5">Wali Murid (Orang Tua)</th>
                        <th class="px-5 py-3.5">Kode QR Unik</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($anakList as $anak)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $anak->nama }}</div>
                                <div class="text-xs text-slate-400">{{ $anak->sekolah->nama ?? 'SD JemputAman' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-800">
                                    {{ $anak->kelas }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-800">{{ $anak->orangTua->name ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $anak->orangTua->no_hp ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-brand-700 font-mono text-xs font-semibold">
                                    <i class="fa-solid fa-qrcode text-xs"></i>
                                    <span>{{ $anak->kode_qr }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center space-x-2">
                                    <!-- Tombol Pratinjau / Cetak Kartu QR -->
                                    <a href="{{ route('admin.anak.kartu', $anak) }}" target="_blank"
                                        title="Cetak Kartu QR Siswa"
                                        class="p-2 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 transition">
                                        <i class="fa-solid fa-address-card text-sm"></i>
                                    </a>

                                    @if(auth()->user()->isAdmin())
                                        <!-- Edit -->
                                        <a href="{{ route('admin.anak.edit', $anak) }}"
                                            title="Edit Data"
                                            class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900 transition">
                                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                                        </a>

                                        <!-- Hapus -->
                                        <form action="{{ route('admin.anak.destroy', $anak) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa {{ $anak->nama }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Data"
                                                class="p-2 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 transition">
                                                <i class="fa-solid fa-trash-can text-sm"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-children text-3xl mb-2 block"></i>
                                Tidak ada data siswa ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($anakList->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $anakList->links() }}
            </div>
        @endif
    </div>
</x-layout>
