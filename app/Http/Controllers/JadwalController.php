<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\Jadwal;
use App\Models\Pengemudi;
use App\Models\Sekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalController extends Controller
{
    /**
     * Tampilkan daftar pola jadwal penjemputan rutin.
     */
    public function index(): View
    {
        $jadwalList = Jadwal::with(['anak.orangTua', 'pengemudi.user', 'sekolah'])->latest()->paginate(10);

        return view('admin.jadwal.index', compact('jadwalList'));
    }

    /**
     * Formulir tambah jadwal penjemputan.
     */
    public function create(): View
    {
        $anakList = Anak::orderBy('nama')->get();
        $pengemudiList = Pengemudi::with('user')->where('aktif', true)->get();
        $sekolahList = Sekolah::orderBy('nama')->get();

        return view('admin.jadwal.create', compact('anakList', 'pengemudiList', 'sekolahList'));
    }

    /**
     * Simpan jadwal penjemputan rutin.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'anak_id' => ['required', 'exists:anak,id'],
            'pengemudi_id' => ['required', 'exists:pengemudi,id'],
            'sekolah_id' => ['required', 'exists:sekolah,id'],
            'hari' => ['required', 'string', 'max:50'],
            'jam_jemput' => ['required', 'string', 'max:10'],
            'titik_jemput' => ['required', 'string', 'max:255'],
            'titik_tujuan' => ['required', 'string', 'max:255'],
            'aktif' => ['boolean'],
        ]);

        $validated['aktif'] = $request->boolean('aktif', true);

        Jadwal::create($validated);

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal penjemputan rutin berhasil ditambahkan!');
    }

    /**
     * Formulir edit jadwal.
     */
    public function edit(Jadwal $jadwal): View
    {
        $anakList = Anak::orderBy('nama')->get();
        $pengemudiList = Pengemudi::with('user')->get();
        $sekolahList = Sekolah::orderBy('nama')->get();

        return view('admin.jadwal.edit', compact('jadwal', 'anakList', 'pengemudiList', 'sekolahList'));
    }

    /**
     * Perbarui jadwal.
     */
    public function update(Request $request, Jadwal $jadwal): RedirectResponse
    {
        $validated = $request->validate([
            'anak_id' => ['required', 'exists:anak,id'],
            'pengemudi_id' => ['required', 'exists:pengemudi,id'],
            'sekolah_id' => ['required', 'exists:sekolah,id'],
            'hari' => ['required', 'string', 'max:50'],
            'jam_jemput' => ['required', 'string', 'max:10'],
            'titik_jemput' => ['required', 'string', 'max:255'],
            'titik_tujuan' => ['required', 'string', 'max:255'],
            'aktif' => ['boolean'],
        ]);

        $validated['aktif'] = $request->boolean('aktif', true);

        $jadwal->update($validated);

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal penjemputan berhasil diperbarui!');
    }

    /**
     * Hapus jadwal.
     */
    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        $jadwal->delete();

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal penjemputan berhasil dihapus.');
    }
}
