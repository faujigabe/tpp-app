<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ViewerAccountSyncService
{
    public function sync(Pegawai $pegawai, bool $resetPassword = false): ?User
    {
        $nip = trim((string) ($pegawai->nip ?? ''));
        $tanggalLahir = $pegawai->tanggal_lahir;

        if ($nip === '' || empty($tanggalLahir)) {
            return null;
        }

        $timestamp = strtotime((string) $tanggalLahir);
        if ($timestamp === false) {
            return null;
        }

        $passwordPlain = date('dmY', $timestamp);
        $sanitizedNip = preg_replace('/[^0-9A-Za-z]/', '', $nip);
        $baseEmail = 'viewer.' . $sanitizedNip . '@local.test';

        $viewer = User::query()
            ->where('pegawai_id', $pegawai->id)
            ->orWhere('email', $baseEmail)
            ->orWhere(function ($query) use ($sanitizedNip) {
                $query->where('role', 'viewer')
                    ->where('email', 'like', 'viewer.' . $sanitizedNip . '.%@local.test');
            })
            ->orderByRaw('CASE WHEN pegawai_id = ? THEN 0 WHEN email = ? THEN 1 ELSE 2 END', [$pegawai->id, $baseEmail])
            ->first();

        if (!$viewer) {
            $viewer = new User();
        }

        $email = $this->resolveUniqueEmail($baseEmail, $viewer->id, $pegawai->id, $viewer);

        $viewer->fill([
            'name' => $pegawai->nama,
            'email' => $email,
            'role' => 'viewer',
            'pegawai_id' => $pegawai->id,
            'unit_kerja_id' => $pegawai->unit_kerja_id,
        ]);

        if (!$viewer->exists || $resetPassword) {
            $viewer->password = Hash::make($passwordPlain);
        }

        $viewer->save();

        return $viewer;
    }

    private function resolveUniqueEmail(string $baseEmail, ?int $currentUserId, int $pegawaiId, ?User $currentUser = null): string
    {
        if ($currentUser && $currentUser->exists && $currentUser->email === $baseEmail) {
            return $baseEmail;
        }

        $query = User::query()->where('email', $baseEmail);
        if ($currentUserId) {
            $query->where('id', '!=', $currentUserId);
        }

        $owner = $query->first();
        if (!$owner) {
            return $baseEmail;
        }

        if (($owner->role ?? null) === 'viewer') {
            return $baseEmail;
        }

        $parts = explode('@', $baseEmail, 2);
        $local = ($parts[0] ?? 'viewer') . '.' . $pegawaiId;
        $domain = $parts[1] ?? 'local.test';
        $candidate = $local . '@' . $domain;

        $check = User::query()->where('email', $candidate);
        if ($currentUserId) {
            $check->where('id', '!=', $currentUserId);
        }
        if (!$check->exists()) {
            return $candidate;
        }

        $counter = 2;
        while (true) {
            $fallback = $local . '.' . $counter . '@' . $domain;
            $fallbackCheck = User::query()->where('email', $fallback);
            if ($currentUserId) {
                $fallbackCheck->where('id', '!=', $currentUserId);
            }
            if (!$fallbackCheck->exists()) {
                return $fallback;
            }
            $counter++;
        }
    }
}
