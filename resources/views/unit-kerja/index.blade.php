@extends('layouts.main')
@section('title', 'Unit Kerja')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h3 class="mb-1">Unit Kerja</h3>
        <p class="text-muted mb-0">Kelola unit kerja agar data pegawai, user, dan TPP terpisah per biro/unit.</p>
    </div>
    <a href="{{ route('unit-kerja.create') }}" class="btn btn-primary btn-icon"><i class="bi bi-plus-circle"></i> Tambah Unit</a>
</div>
<div class="card shadow-soft mb-4"><div class="card-body">
    <form method="GET" class="d-flex gap-2 flex-wrap">
        <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Cari nama atau kode unit..." style="max-width:320px;">
        <button class="btn btn-primary">Cari</button>
        <a href="{{ route('unit-kerja.index') }}" class="btn btn-outline-secondary">Reset</a>
    </form>
</div></div>
<div class="card shadow-soft border-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead class="table-light"><tr><th>Nama Unit</th><th>Kode</th><th class="text-center">User</th><th class="text-center">Pegawai</th><th class="text-center" style="width:170px;">Aksi</th></tr></thead>
<tbody>
@forelse($unitKerjas as $unit)
<tr>
    <td class="fw-semibold">{{ $unit->nama_unit }}</td>
    <td>{{ $unit->kode_unit ?: '-' }}</td>
    <td class="text-center">{{ $unit->users_count }}</td>
    <td class="text-center">{{ $unit->pegawais_count }}</td>
    <td class="text-center">
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('unit-kerja.edit', $unit) }}" class="btn btn-sm btn-warning">Edit</a>
            <form method="POST" action="{{ route('unit-kerja.destroy', $unit) }}" data-confirm data-confirm-title="Hapus unit kerja?" data-confirm-message="Unit {{ $unit->nama_unit }} akan dihapus. Sistem akan menolak jika masih memiliki pengguna, pegawai, atau riwayat terkait." data-confirm-label="Hapus Unit" data-confirm-variant="danger">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Hapus</button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr><td colspan="5" class="text-center py-5 text-muted">Belum ada data unit kerja.</td></tr>
@endforelse
</tbody></table></div>
@if($unitKerjas->hasPages())<div class="card-footer bg-white border-0 py-3">{{ $unitKerjas->links() }}</div>@endif
</div>
@endsection
