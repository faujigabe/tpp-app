<?php

namespace App\Http\Middleware;

use App\Models\Pegawai;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureActiveViewerAccount
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user?->role === 'viewer') {
            $isActive = $user->pegawai()
                ->where('status_pegawai', Pegawai::STATUS_AKTIF)
                ->exists();

            if (! $isActive) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['nip' => 'Akun pegawai tidak aktif. Hubungi pengelola unit kerja.']);
            }
        }

        return $next($request);
    }
}
