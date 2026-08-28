<?php

namespace App\Http\Requests\Auth;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'login_as' => ['required', Rule::in(['pegawai', 'admin'])],
            'nip' => ['required_if:login_as,pegawai', 'nullable', 'string'],
            'email' => ['required_if:login_as,admin', 'nullable', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginAs = $this->string('login_as')->toString();
        $remember = $this->boolean('remember');

        if ($loginAs === 'pegawai') {
            $nip = trim((string) $this->input('nip'));

            $user = User::query()
                ->with('pegawai')
                ->where('role', 'viewer')
                ->whereNotNull('pegawai_id')
                ->whereHas('pegawai', function ($query) use ($nip) {
                    $query->where('nip', $nip)
                        ->where('status_pegawai', Pegawai::STATUS_AKTIF);
                })
                ->first();

            if (! $user || ! Hash::check((string) $this->input('password'), $user->password)) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'nip' => 'NIP atau password tidak sesuai.',
                ]);
            }

            Auth::login($user, $remember);
            RateLimiter::clear($this->throttleKey());

            return;
        }

        $user = User::query()
            ->where('email', trim((string) $this->input('email')))
            ->whereIn('role', ['super_admin', 'admin', 'operator'])
            ->first();

        if (! $user || ! Hash::check((string) $this->input('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak sesuai.',
            ]);
        }

        Auth::login($user, $remember);
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $errorField = $this->identifierField();

        throw ValidationException::withMessages([
            $errorField => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->string('login_as')->toString() . '|' . $this->identifierValue()) . '|' . $this->ip()
        );
    }

    protected function identifierField(): string
    {
        return $this->string('login_as')->toString() === 'pegawai' ? 'nip' : 'email';
    }

    protected function identifierValue(): string
    {
        return trim((string) $this->input($this->identifierField()));
    }
}
