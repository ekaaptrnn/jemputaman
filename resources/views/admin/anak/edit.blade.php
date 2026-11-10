<x-layout title="Edit Siswa — JemputAman">
    <div class="mb-6">
        <a href="{{ route('admin.anak.index') }}" class="inline-flex items-center space-x-1.5 text-sm text-slate-500 hover:text-slate-800 transition mb-2">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Kembali ke Daftar Siswa</span>
        </a>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Data Siswa</h1>
        <p class="text-sm text-slate-500">Perbarui rincian identitas siswa {{ $anak->nama }}.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-2xl">
        <form action="{{ route('admin.anak.update', $anak) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Nama Siswa -->
            <div>
                <label for="nama" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" id="nama" value="{{ old('nama', $anak->nama) }}" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('nama') border-rose-500 @enderror">
            </div>

            <!-- Kelas -->
            <div>
                <label for="kelas" class="block text-sm font-semibold text-slate-700 mb-1.5">Tingkat Kelas <span class="text-rose-500">*</span></label>
                <input type="text" name="kelas" id="kelas" value="{{ old('kelas', $anak->kelas) }}" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('kelas') border-rose-500 @enderror">
            </div>

            <!-- Wali Murid (Orang Tua) -->
            <div>
                <label for="orang_tua_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Wali Murid (Akun Orang Tua) <span class="text-rose-500">*</span></label>
                <select name="orang_tua_id" id="orang_tua_id" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('orang_tua_id') border-rose-500 @enderror">
                    @foreach($orangTuaList as $ortu)
                        <option value="{{ $ortu->id }}" {{ old('orang_tua_id', $anak->orang_tua_id) == $ortu->id ? 'selected' : '' }}>
                            {{ $ortu->name }} ({{ $ortu->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sekolah Pengelola -->
            <div>
                <label for="sekolah_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Sekolah <span class="text-rose-500">*</span></label>
                <select name="sekolah_id" id="sekolah_id" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    @foreach($sekolahList as $sekolah)
                        <option value="{{ $sekolah->id }}" {{ old('sekolah_id', $anak->sekolah_id) == $sekolah->id ? 'selected' : '' }}>
                            {{ $sekolah->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Kode QR Unik -->
            <div class="bg-blue-50/60 p-4 rounded-xl border border-blue-100">
                <label for="kode_qr" class="block text-sm font-semibold text-brand-900 mb-1">
                    Kode QR Unik Siswa <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="kode_qr" id="kode_qr" value="{{ old('kode_qr', $anak->kode_qr) }}" required
                    class="w-full px-4 py-2.5 text-sm font-mono font-semibold bg-white border border-blue-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 text-brand-900 transition @error('kode_qr') border-rose-500 @enderror">
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                <a href="{{ route('admin.anak.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition shadow-sm shadow-brand-500/20">
                    Perbarui Data
                </button>
            </div>
        </form>
    </div>
</x-layout>
