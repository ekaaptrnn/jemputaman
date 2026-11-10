<?php

namespace App\Http\Controllers;

use App\Models\Pengemudi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengemudiController extends Controller
{
    /**
     * Tampilkan daftar driver dan armada sekolah.
     */
    public function index(): View
    {
        $pengemudiList = Pengemudi::with('user')->latest()->paginate(10);

        return view('admin.pengemudi.index', compact('pengemudiList'));
    }

    /**
     * Formulir pendaftaran driver & armada baru.
     */
    public function create(): View
    {
        return view('admin.pengemudi.create');
    }

    /**
     * Simpan akun driver dan data armada kendaraan.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'no_kendaraan' => ['required', 'string', 'max:20'],
            'jenis_kendaraan' => ['required', 'string', 'max:100'],
            'aktif' => ['boolean'],
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'pengemudi',
                'no_hp' => $request->no_hp,
            ]);

            Pengemudi::create([
                'user_id' => $user->id,
                'no_kendaraan' => strtoupper($request->no_kendaraan),
                'jenis_kendaraan' => $request->jenis_kendaraan,
                'aktif' => $request->boolean('aktif', true),
            ]);
        });

        return redirect()->route('admin.pengemudi.index')->with('success', 'Driver dan armada baru berhasil ditambahkan!');
    }

    /**
     * Form edit pengemudi dan armada.
     */
    public function edit(Pengemudi $pengemudi): View
    {
        $pengemudi->load('user');

        return view('admin.pengemudi.edit', compact('pengemudi'));
    }

    /**
     * Perbarui data armada dan akun driver.
     */
    public function update(Request $request, Pengemudi $pengemudi): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($pengemudi->user_id)],
            'password' => ['nullable', 'string', 'min:6'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'no_kendaraan' => ['required', 'string', 'max:20'],
            'jenis_kendaraan' => ['required', 'string', 'max:100'],
            'aktif' => ['boolean'],
        ]);

        DB::transaction(function () use ($request, $pengemudi) {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'no_hp' => $request->no_hp,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $pengemudi->user->update($userData);

            $pengemudi->update([
                'no_kendaraan' => strtoupper($request->no_kendaraan),
                'jenis_kendaraan' => $request->jenis_kendaraan,
                'aktif' => $request->boolean('aktif', true),
            ]);
        });

        return redirect()->route('admin.pengemudi.index')->with('success', 'Data armada driver berhasil diperbarui!');
    }

    /**
     * Hapus data pengemudi.
     */
    public function destroy(Pengemudi $pengemudi): RedirectResponse
    {
        DB::transaction(function () use ($pengemudi) {
            $user = $pengemudi->user;
            $pengemudi->delete();
            if ($user) {
                $user->delete();
            }
        });

        return redirect()->route('admin.pengemudi.index')->with('success', 'Data pengemudi dan armada berhasil dihapus.');
    }
}
