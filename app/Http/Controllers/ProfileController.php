<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->loadMissing('pegawai.kelasJabatan'),
        ]);
    }

    public function photo(Request $request)
    {
        $user = $request->user()->loadMissing('pegawai');
        $photoPath = $user->pegawai?->foto_profil ?: $user->foto_profil;

        abort_unless($photoPath, 404);

        $path = Storage::disk('public')->path($photoPath);
        abort_unless(is_file($path), 404);

        return response()->file($path);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user()->loadMissing('pegawai');
        $validated = $request->validated();

        DB::transaction(function () use ($request, $user, $validated) {
            $user->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'nip' => $user->pegawai ? ($user->nip ?? null) : ($validated['nip'] ?? $user->nip),
            ]);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            if ($request->boolean('hapus_foto_profil') && !$user->pegawai && $user->foto_profil) {
                Storage::disk('public')->delete($user->foto_profil);
                $user->foto_profil = null;
            }

            if ($request->hasFile('foto_profil') && !$user->pegawai) {
                if ($user->foto_profil) {
                    Storage::disk('public')->delete($user->foto_profil);
                }

                $user->foto_profil = $request->file('foto_profil')->store('foto-profil', 'public');
            }

            $user->save();

            if ($user->pegawai) {
                $pegawaiPayload = [
                    'nama' => $validated['name'],
                    'nip' => $validated['nip'] ?? $user->pegawai->nip,
                    'nik' => $validated['nik'] ?? null,
                    'no_npwp' => $validated['no_npwp'] ?? null,
                    'nomor_rekening' => $validated['nomor_rekening'] ?? null,
                    'alamat' => $validated['alamat'] ?? null,
                    'no_hp' => $validated['no_hp'] ?? null,
                    'golongan' => $validated['golongan'] ?? null,
                    'jabatan' => $validated['jabatan'] ?? null,
                ];

                if ($request->boolean('hapus_foto_profil')) {
                    if ($user->pegawai->foto_profil) {
                        Storage::disk('public')->delete($user->pegawai->foto_profil);
                    }

                    $pegawaiPayload['foto_profil'] = null;
                }

                if ($request->hasFile('foto_profil')) {
                    if ($user->pegawai->foto_profil) {
                        Storage::disk('public')->delete($user->pegawai->foto_profil);
                    }

                    $pegawaiPayload['foto_profil'] = $request->file('foto_profil')->store('foto-profil', 'public');
                }

                $user->pegawai->update($pegawaiPayload);
            }
        });

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user()->loadMissing('pegawai');

        Auth::logout();

        if ($user->pegawai?->foto_profil) {
            Storage::disk('public')->delete($user->pegawai->foto_profil);
        }

        if ($user->foto_profil) {
            Storage::disk('public')->delete($user->foto_profil);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
