<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AnakController extends Controller
{
    /**
     * Tampilkan daftar siswa.
     */
    public function index(Request $request): View
    {
        $query = Anak::with(['orangTua', 'sekolah'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kelas', 'like', "%{$search}%")
                  ->orWhere('kode_qr', 'like', "%{$search}%");
            });
        }

        $anakList = $query->paginate(10)->withQueryString();

        return view('admin.anak.index', compact('anakList'));
    }

    /**
     * Tampilkan formulir tambah siswa.
     */
    public function create(): View
    {
        $orangTuaList = User::where('role', 'orang_tua')->orderBy('name')->get();
        $sekolahList = Sekolah::orderBy('nama')->get();
        $autoKodeQr = 'QR-' . strtoupper(Str::random(8));

        return view('admin.anak.create', compact('orangTuaList', 'sekolahList', 'autoKodeQr'));
    }

    /**
     * Simpan data siswa baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kelas' => ['required', 'string', 'max:50'],
            'orang_tua_id' => ['required', 'exists:users,id'],
            'sekolah_id' => ['required', 'exists:sekolah,id'],
            'kode_qr' => ['required', 'string', 'max:100', 'unique:anak,kode_qr'],
        ], [
            'kode_qr.unique' => 'Kode QR ini sudah digunakan oleh siswa lain.',
        ]);

        Anak::create($validated);

        return redirect()->route('admin.anak.index')->with('success', 'Data siswa berhasil ditambahkan!');
    }

    /**
     * Tampilkan form edit siswa.
     */
    public function edit(Anak $anak): View
    {
        $orangTuaList = User::where('role', 'orang_tua')->orderBy('name')->get();
        $sekolahList = Sekolah::orderBy('nama')->get();

        return view('admin.anak.edit', compact('anak', 'orangTuaList', 'sekolahList'));
    }

    /**
     * Perbarui data siswa.
     */
    public function update(Request $request, Anak $anak): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kelas' => ['required', 'string', 'max:50'],
            'orang_tua_id' => ['required', 'exists:users,id'],
            'sekolah_id' => ['required', 'exists:sekolah,id'],
            'kode_qr' => ['required', 'string', 'max:100', 'unique:anak,kode_qr,' . $anak->id],
        ]);

        $anak->update($validated);

        return redirect()->route('admin.anak.index')->with('success', 'Data siswa berhasil diperbarui!');
    }

    /**
     * Hapus data siswa.
     */
    public function destroy(Anak $anak): RedirectResponse
    {
        $anak->delete();

        return redirect()->route('admin.anak.index')->with('success', 'Data siswa berhasil dihapus.');
    }

    /**
     * Cetak atau tampilkan Kartu ID Siswa ber-QR.
     */
    public function kartu(Anak $anak): View
    {
        $anak->load(['orangTua', 'sekolah']);

        return view('admin.anak.kartu', compact('anak'));
    }
}
