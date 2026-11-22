<x-layout title="Tambah Jadwal Rutin — JemputAman">
    <div class="mb-6">
        <a href="{{ route('admin.jadwal.index') }}" class="inline-flex items-center space-x-1.5 text-sm text-slate-500 hover:text-slate-800 transition mb-2">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Kembali ke Daftar Jadwal</span>
        </a>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Atur Pola Jadwal Penjemputan</h1>
        <p class="text-sm text-slate-500">Pasangkan anak didik dengan armada penjemput dan rute harian kepulangan.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-2xl">
        <form action="{{ route('admin.jadwal.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Siswa -->
            <div>
                <label for="anak_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Pilih Siswa <span class="text-rose-500">*</span></label>
                <select name="anak_id" id="anak_id" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('anak_id') border-rose-500 @enderror">
                    <option value="">-- Pilih Siswa --</option>
                    @foreach($anakList as $anak)
                        <option value="{{ $anak->id }}" {{ old('anak_id') == $anak->id ? 'selected' : '' }}>
                            {{ $anak->nama }} (Kelas {{ $anak->kelas }} · {{ $anak->kode_qr }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Pengemudi & Armada -->
            <div>
                <label for="pengemudi_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Pilih Driver & Armada Kendaraan <span class="text-rose-500">*</span></label>
                <select name="pengemudi_id" id="pengemudi_id" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('pengemudi_id') border-rose-500 @enderror">
                    <option value="">-- Pilih Driver Armada --</option>
                    @foreach($pengemudiList as $p)
                        <option value="{{ $p->id }}" {{ old('pengemudi_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->user->name ?? 'Driver' }} ({{ $p->jenis_kendaraan }} - {{ $p->no_kendaraan }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sekolah Asal -->
            <div>
                <label for="sekolah_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Sekolah <span class="text-rose-500">*</span></label>
                <select name="sekolah_id" id="sekolah_id" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    @foreach($sekolahList as $sekolah)
                        <option value="{{ $sekolah->id }}" {{ old('sekolah_id') == $sekolah->id ? 'selected' : '' }}>
                            {{ $sekolah->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Hari -->
                <div>
                    <label for="hari" class="block text-sm font-semibold text-slate-700 mb-1.5">Hari Operasional <span class="text-rose-500">*</span></label>
                    <input type="text" name="hari" id="hari" value="{{ old('hari', 'Senin - Jumat') }}" required
                        placeholder="Contoh: Senin - Jumat"
                        class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('hari') border-rose-500 @enderror">
                </div>

                <!-- Jam Jemput -->
                <div>
                    <label for="jam_jemput" class="block text-sm font-semibold text-slate-700 mb-1.5">Jam Jemput Sekolah <span class="text-rose-500">*</span></label>
                    <input type="text" name="jam_jemput" id="jam_jemput" value="{{ old('jam_jemput', '13:00') }}" required
                        placeholder="Contoh: 13:00"
                        class="w-full px-4 py-2.5 text-sm font-mono bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('jam_jemput') border-rose-500 @enderror">
                </div>
            </div>

            <!-- Titik Jemput -->
            <div>
                <label for="titik_jemput" class="block text-sm font-semibold text-slate-700 mb-1.5">Titik Penjemputan di Sekolah <span class="text-rose-500">*</span></label>
                <input type="text" name="titik_jemput" id="titik_jemput" value="{{ old('titik_jemput', 'Gerbang Utama SD JemputAman') }}" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('titik_jemput') border-rose-500 @enderror">
            </div>

            <!-- Titik Tujuan (Alamat Rumah) -->
            <div>
                <label for="titik_tujuan" class="block text-sm font-semibold text-slate-700 mb-1.5">Titik Tujuan / Alamat Rumah Siswa <span class="text-rose-500">*</span></label>
                <input type="text" name="titik_tujuan" id="titik_tujuan" value="{{ old('titik_tujuan') }}" required
                    placeholder="Contoh: Jl. Mawar No. 12, Laweyan, Surakarta"
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('titik_tujuan') border-rose-500 @enderror">
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" name="aktif" id="aktif" value="1" {{ old('aktif', '1') == '1' ? 'checked' : '' }}
                    class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                <label for="aktif" class="text-sm font-semibold text-slate-700">Jadwal penjemputan rutin aktif</label>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                <a href="{{ route('admin.jadwal.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold transition shadow-sm shadow-purple-500/20">
                    Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</x-layout>
