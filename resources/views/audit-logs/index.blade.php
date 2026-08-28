@extends('layouts.main')

@section('title', 'Jejak Perubahan')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h3 class="mb-1">Jejak Perubahan Data</h3>
        <p class="text-muted mb-0">Riwayat terstruktur perubahan data penting. Password dan token tidak pernah disimpan di sini.</p>
    </div>
    <span class="badge text-bg-dark px-3 py-2">Retensi 5 tahun</span>
</div>

<div class="card shadow-soft border-0 mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Pencarian</label>
                <input name="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="Pelaku, ID data, atau IP">
            </div>
            <div class="col-md-2">
                <label class="form-label">Aksi</label>
                <select name="event" class="form-select">
                    <option value="">Semua aksi</option>
                    @foreach(['created' => 'Dibuat', 'updated' => 'Diperbarui', 'deleted' => 'Dihapus'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['event'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Jenis Data</label>
                <select name="model" class="form-select">
                    <option value="">Semua data</option>
                    @foreach($models as $model)
                        <option value="{{ $model }}" @selected(($filters['model'] ?? '') === $model)>{{ class_basename($model) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Unit Kerja</label>
                <select name="unit_kerja_id" class="form-select">
                    <option value="">Semua unit</option>
                    @foreach($unitKerjas as $unit)
                        <option value="{{ $unit->id }}" @selected((string) ($filters['unit_kerja_id'] ?? '') === (string) $unit->id)>{{ $unit->nama_unit }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1">Terapkan</button>
                <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
            <div class="col-md-2">
                <label class="form-label">Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control" value="{{ $filters['tanggal_mulai'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control" value="{{ $filters['tanggal_selesai'] ?? '' }}">
            </div>
        </form>
    </div>
</div>

<div class="card shadow-soft border-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Waktu</th><th>Pelaku</th><th>Aksi</th><th>Data</th><th>Unit</th><th>Perubahan</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td class="text-nowrap">{{ $log->created_at?->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') }}</td>
                    <td>
                        <div class="fw-semibold">{{ $log->actor_name ?: 'Sistem/CLI' }}</div>
                        <small class="text-muted">{{ $log->actor_role ?: '-' }}{{ $log->ip_address ? ' · ' . $log->ip_address : '' }}</small>
                    </td>
                    <td><span class="badge {{ $log->event === 'deleted' ? 'text-bg-danger' : ($log->event === 'created' ? 'text-bg-success' : 'text-bg-warning') }}">{{ ['created'=>'Dibuat','updated'=>'Diperbarui','deleted'=>'Dihapus'][$log->event] ?? $log->event }}</span></td>
                    <td>{{ $log->subjectLabel() }}</td>
                    <td>{{ $log->unitKerja?->nama_unit ?? '-' }}</td>
                    <td style="min-width:300px">
                        <details>
                            <summary class="text-primary" style="cursor:pointer">Lihat nilai sebelum–sesudah</summary>
                            <div class="row g-2 mt-1">
                                <div class="col-lg-6"><small class="text-muted">Sebelum</small><pre class="small bg-light border rounded p-2 mb-0">{{ json_encode($log->old_values, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) ?: '-' }}</pre></div>
                                <div class="col-lg-6"><small class="text-muted">Sesudah</small><pre class="small bg-light border rounded p-2 mb-0">{{ json_encode($log->new_values, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) ?: '-' }}</pre></div>
                            </div>
                        </details>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">Belum ada jejak perubahan sesuai filter.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())<div class="card-footer bg-white">{{ $logs->links() }}</div>@endif
</div>
@endsection
