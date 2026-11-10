<x-layout title="Edit Driver & Armada — JemputAman">
    <div class="mb-6">
        <a href="{{ route('admin.pengemudi.index') }}" class="inline-flex items-center space-x-1.5 text-sm text-slate-500 hover:text-slate-800 transition mb-2">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Kembali ke Daftar Driver</span>
        </a>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Driver & Armada</h1>
        <p class="text-sm text-slate-500">Perbarui rincian armada {{ $pengemudi->no_kendaraan }} dan akun {{ $pengemudi->user->name ?? '' }}.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-2xl">
        <form action="{{ route('admin.pengemudi.update', $pengemudi) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Nama Driver -->
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap Driver <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $pengemudi->user->name ?? '') }}" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('name') border-rose-500 @enderror">
            </div>

            <!-- Email Driver -->
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Email Login <span class="text-rose-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email', $pengemudi->user->email ?? '') }}" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('email') border-rose-500 @enderror">
            </div>

            <!-- Password Baru (Opsional) -->
            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Password Baru (Kosongkan jika tidak ingin diubah)</label>
                <input type="password" name="password" id="password"
                    placeholder="Minimal 6 karakter"
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('password') border-rose-500 @enderror">
            </div>

            <!-- No HP -->
            <div>
                <label for="no_hp" class="block text-sm font-semibold text-slate-700 mb-1.5">Nomor Handphone / WhatsApp</label>
                <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp', $pengemudi->user->no_hp ?? '') }}"
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <hr class="border-slate-100">

            <!-- Plat Nomor Kendaraan -->
            <div>
                <label for="no_kendaraan" class="block text-sm font-semibold text-slate-700 mb-1.5">Nomor Plat Polisi <span class="text-rose-500">*</span></label>
                <input type="text" name="no_kendaraan" id="no_kendaraan" value="{{ old('no_kendaraan', $pengemudi->no_kendaraan) }}" required
                    class="w-full px-4 py-2.5 text-sm font-mono uppercase bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('no_kendaraan') border-rose-500 @enderror">
            </div>

            <!-- Jenis Kendaraan -->
            <div>
                <label for="jenis_kendaraan" class="block text-sm font-semibold text-slate-700 mb-1.5">Tipe & Jenis Kendaraan <span class="text-rose-500">*</span></label>
                <input type="text" name="jenis_kendaraan" id="jenis_kendaraan" value="{{ old('jenis_kendaraan', $pengemudi->jenis_kendaraan) }}" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('jenis_kendaraan') border-rose-500 @enderror">
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" name="aktif" id="aktif" value="1" {{ old('aktif', $pengemudi->aktif) ? 'checked' : '' }}
                    class="w-4 h-4 text-brand-600 rounded focus:ring-brand-500">
                <label for="aktif" class="text-sm font-semibold text-slate-700">Status armada aktif dan siap ditugaskan</label>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                <a href="{{ route('admin.pengemudi.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold transition shadow-sm shadow-amber-500/20">
                    Perbarui Driver & Armada
                </button>
            </div>
        </form>
    </div>
</x-layout>
