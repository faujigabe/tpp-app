<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $user = $this->user();
        $pegawaiId = optional($user->pegawai)->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user?->id)],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_foto_profil' => ['nullable', 'boolean'],
            'nip' => [
                Rule::requiredIf(fn () => (bool) $pegawaiId),
                'nullable',
                'string',
                'max:50',
                Rule::when((bool) $pegawaiId,
                    Rule::unique('pegawais', 'nip')->ignore($pegawaiId),
                    Rule::unique('users', 'nip')->ignore($user?->id)
                ),
            ],
            'nik' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('pegawais', 'nik')->ignore($pegawaiId),
            ],
            'no_npwp' => ['nullable', 'string', 'max:50'],
            'nomor_rekening' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'golongan' => ['nullable', Rule::in(['II/A','II/B','II/C','II/D','III/A','III/B','III/C','III/D','IV/A','IV/B','IV/C','IV/D','IV/E'])],
            'jabatan' => ['nullable', 'string', 'max:255'],
        ];
    }
}
