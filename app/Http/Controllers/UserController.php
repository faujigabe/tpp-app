<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $actor = $request->user();
        $selectedUnitKerjaId = $actor->isSuperAdmin()
            ? ($request->filled('unit_kerja_id') ? (int) $request->integer('unit_kerja_id') : null)
            : (int) $actor->unit_kerja_id;

        $users = User::query()
            ->with(['pegawai.unitKerja', 'unitKerja'])
            ->when(! $actor->isSuperAdmin(), fn ($query) => $query->where('unit_kerja_id', $actor->unit_kerja_id))
            ->when($actor->isSuperAdmin() && $selectedUnitKerjaId, fn ($query) => $query->where('unit_kerja_id', $selectedUnitKerjaId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%")
                        ->orWhereHas('pegawai', function ($pegawaiQuery) use ($search) {
                            $pegawaiQuery->where('nama', 'like', "%{$search}%")
                                ->orWhere('nip', 'like', "%{$search}%");
                        })
                        ->orWhereHas('unitKerja', function ($unitQuery) use ($search) {
                            $unitQuery->where('nama_unit', 'like', "%{$search}%")
                                ->orWhere('kode_unit', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByRaw("CASE WHEN role = 'super_admin' THEN 0 WHEN role = 'admin' THEN 1 WHEN role = 'operator' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $unitKerjas = $actor->isSuperAdmin()
            ? UnitKerja::orderBy('nama_unit')->get()
            : UnitKerja::whereKey($actor->unit_kerja_id)->get();

        return view('users.index', compact('users', 'search', 'unitKerjas', 'selectedUnitKerjaId'));
    }

    public function create(Request $request)
    {
        $actor = $request->user();
        $unitKerjas = $this->availableUnitKerjas($actor);
        $selectedUnitKerjaId = $actor->isSuperAdmin()
            ? old('unit_kerja_id', $request->get('unit_kerja_id', $actor->unit_kerja_id))
            : (int) $actor->unit_kerja_id;
        $pegawais = $this->availablePegawais($selectedUnitKerjaId);
        $allowedRoles = $this->allowedRoles($actor);

        return view('users.create', compact('pegawais', 'unitKerjas', 'selectedUnitKerjaId', 'allowedRoles'));
    }

    public function store(Request $request)
    {
        $actor = $request->user();
        $validated = $this->validateUser($request);
        $unitKerjaId = $this->resolveUnitKerjaId($actor, $validated);
        $this->ensureRoleCanBeAssigned($actor, $validated['role']);
        $pegawaiId = $this->resolvePegawaiId($validated);

        if ($pegawaiId) {
            $pegawai = Pegawai::findOrFail($pegawaiId);
            if ((int) $pegawai->unit_kerja_id !== $unitKerjaId) {
                return back()->withInput()->withErrors(['pegawai_id' => 'Pegawai harus berasal dari unit kerja yang sama.']);
            }
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'pegawai_id' => $pegawaiId,
            'unit_kerja_id' => $unitKerjaId,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat.');
    }

    public function edit(Request $request, User $user)
    {
        $actor = $request->user();
        $this->ensureManageableUser($actor, $user);

        $unitKerjas = $this->availableUnitKerjas($actor);
        $selectedUnitKerjaId = $actor->isSuperAdmin()
            ? old('unit_kerja_id', $user->unit_kerja_id)
            : (int) $actor->unit_kerja_id;
        $pegawais = $this->availablePegawais($selectedUnitKerjaId, $user->id);
        $allowedRoles = $this->allowedRoles($actor);

        return view('users.edit', compact('user', 'pegawais', 'unitKerjas', 'selectedUnitKerjaId', 'allowedRoles'));
    }

    public function update(Request $request, User $user)
    {
        $actor = $request->user();
        $this->ensureManageableUser($actor, $user);

        $validated = $this->validateUser($request, $user);
        $unitKerjaId = $this->resolveUnitKerjaId($actor, $validated, $user->unit_kerja_id);
        $this->ensureRoleCanBeAssigned($actor, $validated['role']);
        $pegawaiId = $this->resolvePegawaiId($validated);

        if ($pegawaiId) {
            $pegawai = Pegawai::findOrFail($pegawaiId);
            if ((int) $pegawai->unit_kerja_id !== $unitKerjaId) {
                return back()->withInput()->withErrors(['pegawai_id' => 'Pegawai harus berasal dari unit kerja yang sama.']);
            }
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'pegawai_id' => $pegawaiId,
            'unit_kerja_id' => $unitKerjaId,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        $actor = $request->user();
        $this->ensureManageableUser($actor, $user);

        if ($actor->id === $user->id) {
            return back()->with('error', 'Akun yang sedang login tidak dapat dihapus.');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
            'role' => 'required|in:super_admin,admin,operator,viewer',
            'unit_kerja_id' => 'nullable|exists:unit_kerjas,id',
            'pegawai_id' => [
                Rule::requiredIf(fn () => $request->role === 'viewer'),
                'nullable',
                'exists:pegawais,id',
                Rule::unique('users', 'pegawai_id')->ignore($user?->id),
            ],
        ], [
            'pegawai_id.required' => 'Pegawai wajib dipilih untuk akun viewer.',
            'pegawai_id.unique' => 'Pegawai ini sudah terhubung dengan akun user lain.',
        ]);
    }

    private function availablePegawais(?int $unitKerjaId, ?int $ignoreUserId = null)
    {
        $linkedPegawaiIds = User::query()
            ->when($ignoreUserId, fn ($query) => $query->where('id', '!=', $ignoreUserId))
            ->whereNotNull('pegawai_id')
            ->pluck('pegawai_id');

        return Pegawai::query()
            ->when($unitKerjaId, fn ($query) => $query->where('unit_kerja_id', $unitKerjaId))
            ->when($linkedPegawaiIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $linkedPegawaiIds))
            ->orderBy('nama')
            ->get();
    }

    private function availableUnitKerjas(User $actor)
    {
        return UnitKerja::query()
            ->when(! $actor->isSuperAdmin(), fn ($query) => $query->whereKey($actor->unit_kerja_id))
            ->orderBy('nama_unit')
            ->get();
    }

    private function allowedRoles(User $actor): array
    {
        return $actor->isSuperAdmin()
            ? ['super_admin', 'admin', 'operator', 'viewer']
            : ['admin', 'operator', 'viewer'];
    }

    private function ensureRoleCanBeAssigned(User $actor, string $role): void
    {
        abort_unless(in_array($role, $this->allowedRoles($actor), true), 403, 'Akses ditolak');
    }

    private function ensureManageableUser(User $actor, User $target): void
    {
        if ($actor->isSuperAdmin()) {
            return;
        }

        abort_if($target->role === 'super_admin', 403, 'Akses ditolak');
        abort_unless($actor->canAccessUnit($target->unit_kerja_id), 403, 'Akses ditolak');
    }

    private function resolveUnitKerjaId(User $actor, array $validated, ?int $fallback = null): int
    {
        if ($actor->isSuperAdmin()) {
            return (int) ($validated['unit_kerja_id'] ?? $fallback);
        }

        return (int) $actor->unit_kerja_id;
    }

    private function resolvePegawaiId(array $validated): ?int
    {
        return $validated['role'] === 'viewer' ? ($validated['pegawai_id'] ? (int) $validated['pegawai_id'] : null) : null;
    }
}
