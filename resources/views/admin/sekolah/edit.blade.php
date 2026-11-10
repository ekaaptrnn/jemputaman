<x-layout title="Profil Sekolah — JemputAman">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Profil Sekolah Pengelola Armada</h1>
        <p class="text-sm text-slate-500">Kelola informasi sekolah resmi penyelenggara operasional antar-jemput SD JemputAman.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-2xl">
        <form action="{{ route('admin.sekolah.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Nama Sekolah -->
            <div>
                <label for="nama" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Resmi Sekolah <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" id="nama" value="{{ old('nama', $sekolah->nama) }}" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('nama') border-rose-500 @enderror">
            </div>

            <!-- Alamat Sekolah -->
            <div>
                <label for="alamat" class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Lengkap Sekolah <span class="text-rose-500">*</span></label>
                <textarea name="alamat" id="alamat" rows="3" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('alamat') border-rose-500 @enderror">{{ old('alamat', $sekolah->alamat) }}</textarea>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition shadow-sm shadow-brand-500/20">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-layout>
