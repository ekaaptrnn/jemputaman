<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SekolahController extends Controller
{
    /**
     * Tampilkan formulir edit profil sekolah utama.
     */
    public function edit(): View
    {
        $sekolah = Sekolah::firstOrCreate(
            ['id' => 1],
            [
                'nama' => 'SD JemputAman Surakarta',
                'alamat' => 'Jl. Slamet Riyadi No. 120, Kota Surakarta, Jawa Tengah',
            ]
        );

        return view('admin.sekolah.edit', compact('sekolah'));
    }

    /**
     * Perbarui data profil sekolah.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:500'],
        ]);

        $sekolah = Sekolah::first();
        if ($sekolah) {
            $sekolah->update($validated);
        } else {
            Sekolah::create($validated);
        }

        return redirect()->route('admin.sekolah.edit')->with('success', 'Profil sekolah berhasil diperbarui!');
    }
}
