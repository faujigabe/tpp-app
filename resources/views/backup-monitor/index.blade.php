@extends('layouts.main')
@section('title', 'Monitoring Backup')

@section('content')
@php
    $formatSize = function ($bytes) {
        if ($bytes === null) return '-';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
        return number_format($bytes / 1024, 2, ',', '.') . ' KB';
    };
@endphp

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Monitoring Backup</h2>
        <p class="text-muted mb-0">Pantau usia file, checksum, ukuran, dan riwayat proses backup database.</p>
    </div>
    <span class="badge fs-6 {{ $health['healthy'] ? 'text-bg-success' : 'text-bg-danger' }}">
        <i class="bi {{ $health['healthy'] ? 'bi-shield-check' : 'bi-exclamation-triangle' }} me-1"></i>
        {{ $health['healthy'] ? 'Backup Sehat' : 'Perlu Perhatian' }}
    </span>
</div>

<div class="row g-4 mb-4">
    @foreach(['daily' => 'Backup Harian', 'weekly' => 'Backup Mingguan'] as $key => $label)
        @php($item = $health[$key])
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-muted small">{{ $label }}</div>
                            <h5 class="mb-0 text-break">{{ $item['file'] ?? 'Belum ditemukan' }}</h5>
                        </div>
                        <span class="badge {{ $item['healthy'] ? 'text-bg-success' : 'text-bg-danger' }}">
                            {{ $item['healthy'] ? 'Sehat' : 'Bermasalah' }}
                        </span>
                    </div>
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4">Terakhir dibuat</dt>
                        <dd class="col-sm-8">{{ $item['modified_at']?->format('d/m/Y H:i:s') ?? '-' }}</dd>
                        <dt class="col-sm-4">Usia</dt>
                        <dd class="col-sm-8">{{ $item['age_hours'] !== null ? $item['age_hours'] . ' jam' : '-' }} (maks. {{ $item['max_age_hours'] }} jam)</dd>
                        <dt class="col-sm-4">Ukuran</dt>
                        <dd class="col-sm-8">{{ $formatSize($item['size_bytes']) }}</dd>
                        <dt class="col-sm-4">Checksum</dt>
                        <dd class="col-sm-8">{{ $item['checksum_valid'] ? 'Valid' : ($item['checksum_exists'] ? 'Tidak valid' : 'Tidak tersedia') }}</dd>
                        <dt class="col-sm-4">Lokasi</dt>
                        <dd class="col-sm-8 text-break">{{ $item['path'] ?? 'Belum dikonfigurasi' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 p-4 pb-0">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <h5 class="mb-1">Riwayat Proses Backup</h5>
                <p class="text-muted mb-0">Catatan ini dimulai setelah fitur monitoring diaktifkan.</p>
            </div>
            <form method="GET" class="d-flex flex-wrap gap-2">
                <select name="type" class="form-select form-select-sm" aria-label="Jenis backup">
                    <option value="">Semua jenis</option>
                    <option value="daily" @selected($typeFilter === 'daily')>Harian</option>
                    <option value="weekly" @selected($typeFilter === 'weekly')>Mingguan</option>
                </select>
                <select name="status" class="form-select form-select-sm" aria-label="Status backup">
                    <option value="">Semua status</option>
                    <option value="success" @selected($statusFilter === 'success')>Berhasil</option>
                    <option value="failed" @selected($statusFilter === 'failed')>Gagal</option>
                    <option value="running" @selected($statusFilter === 'running')>Berjalan</option>
                </select>
                <button class="btn btn-sm btn-primary" type="submit">Filter</button>
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Mulai</th>
                    <th>Jenis</th>
                    <th>Status</th>
                    <th>File</th>
                    <th>Ukuran</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($runs as $run)
                    <tr>
                        <td>{{ $run->started_at?->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $run->type === 'weekly' ? 'Mingguan' : 'Harian' }}</td>
                        <td>
                            <span class="badge {{ $run->status === 'success' ? 'text-bg-success' : ($run->status === 'failed' ? 'text-bg-danger' : 'text-bg-warning') }}">
                                {{ ['success' => 'Berhasil', 'failed' => 'Gagal', 'running' => 'Berjalan'][$run->status] ?? $run->status }}
                            </span>
                        </td>
                        <td class="text-break">{{ $run->file_path ? basename($run->file_path) : '-' }}</td>
                        <td>{{ $formatSize($run->size_bytes) }}</td>
                        <td class="text-break">{{ $run->error_message ?: ($run->checksum ? 'Checksum ' . substr($run->checksum, 0, 12) . '…' : '-') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">Belum ada riwayat proses backup.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($runs->hasPages())
        <div class="card-footer bg-white">{{ $runs->links() }}</div>
    @endif
</div>
@endsection
