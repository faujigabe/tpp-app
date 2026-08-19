@extends('layouts.main')

@section('title', 'Data Pegawai')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h3 class="mb-1">Data Pegawai</h3>
        <div class="text-muted">Kelola data pegawai, cari cepat berdasarkan nama atau NIP, lalu filter data berdasarkan kelengkapan foto profil dan NPWP.</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('pegawai.import.form') }}" class="btn btn-outline-success btn-icon">
            <i class="bi bi-file-earmark-arrow-up"></i><span>Import Pegawai</span>
        </a>
        <a href="{{ route('pegawai.create') }}" class="btn btn-primary btn-icon">
            <i class="bi bi-plus-circle"></i><span>Tambah Pegawai</span>
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-soft h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Pegawai</div>
                <div class="fs-3 fw-bold">{{ number_format($totalPegawai, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-soft h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Sudah Punya Kelas Jabatan</div>
                <div class="fs-3 fw-bold text-success">{{ number_format($totalPegawai - $tanpaKelas, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-soft h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Sudah Upload Foto</div>
                <div class="fs-3 fw-bold text-primary">{{ number_format($totalSudahFoto, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-soft h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Sudah Isi NPWP</div>
                <div class="fs-3 fw-bold text-info">{{ number_format($totalSudahNpwp, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-soft h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Pegawai Aktif</div>
                <div class="fs-3 fw-bold text-success">{{ number_format($totalAktif, 0, ',', '.') }}</div>
                <div class="small text-muted">Nonaktif: {{ number_format($totalNonaktif, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-soft mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('pegawai.index') }}" class="row g-3 align-items-end">
            <div class="col-lg-3">
                <label class="form-label">Cari Pegawai</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="Cari berdasarkan nama, NIP, atau NIK..." value="{{ $search }}">
                </div>
            </div>
            @if(auth()->user()->isSuperAdmin())
            <div class="col-lg-3">
                <label class="form-label">Unit Kerja</label>
                <select name="unit_kerja_id" class="form-select">
                    <option value="">Semua Unit Kerja</option>
                    @foreach($unitKerjas as $unit)
                        <option value="{{ $unit->id }}" @selected((string) $selectedUnitKerjaId === (string) $unit->id)>{{ $unit->nama_unit }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-lg-3">
                <label class="form-label">Filter Foto Profil</label>
                <select name="foto" class="form-select">
                    <option value="">Semua Kondisi Foto</option>
                    <option value="sudah" @selected($fotoFilter === 'sudah')>Sudah punya foto</option>
                    <option value="belum" @selected($fotoFilter === 'belum')>Belum punya foto</option>
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Filter NPWP</label>
                <select name="npwp" class="form-select">
                    <option value="">Semua Kondisi NPWP</option>
                    <option value="sudah" @selected($npwpFilter === 'sudah')>Sudah isi NPWP</option>
                    <option value="belum" @selected($npwpFilter === 'belum')>Belum isi NPWP</option>
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Status Pegawai</label>
                <select name="status" class="form-select">
                    <option value="" @selected($statusFilter === '')>Semua Status</option>
                    <option value="aktif" @selected($statusFilter === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($statusFilter === 'nonaktif')>Semua Nonaktif</option>
                    @foreach($statusOptions as $statusValue => $statusLabel)
                        @if($statusValue !== 'aktif')
                            <option value="{{ $statusValue }}" @selected($statusFilter === $statusValue)>{{ $statusLabel }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-icon w-100">
                    <i class="bi bi-funnel"></i><span>Terapkan</span>
                </button>
                <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary btn-icon w-100">
                    <i class="bi bi-arrow-clockwise"></i><span>Reset</span>
                </a>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2 pt-1">
                <a href="{{ route('pegawai.template') }}" class="btn btn-outline-dark btn-icon ms-lg-auto">
                    <i class="bi bi-download"></i><span>Template Import</span>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-soft border-0">
        <div class="card-header bg-white d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 py-3">
            <div>
                <div class="fw-semibold">Daftar Pegawai</div>
                <div class="small text-muted">Menampilkan {{ $pegawais->total() }} data{{ $search || $fotoFilter || $npwpFilter || $selectedUnitKerjaId ? ' sesuai filter aktif' : '' }}. @if(auth()->user()->isSuperAdmin()){{ $selectedUnitKerjaId ? 'Filter unit kerja sedang aktif.' : 'Saat ini menampilkan seluruh unit kerja.' }}@else Data pegawai dibatasi untuk unit kerja Anda.@endif</div>
            </div>
            <button type="submit" form="mass-delete-form" class="btn btn-outline-danger btn-sm btn-icon" id="btn-delete-selected" disabled onclick="return confirm('Hapus semua pegawai yang dipilih?')">
                <i class="bi bi-trash3"></i><span>Hapus Massal</span>
            </button>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 52px;">
                                <input class="form-check-input" type="checkbox" id="check-all">
                            </th>
                            <th>Pegawai</th>
                            <th>Kontak</th>
                            <th>Administrasi</th>
                            <th>Golongan</th>
                            <th>Jabatan</th>
                            <th>Agama</th>
                            <th>Kelas Jabatan</th>
                            <th>Unit Kerja</th>
                            <th class="text-center" style="width: 170px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pegawais as $pegawai)
                            <tr>
                                <td class="text-center">
                                    <input class="form-check-input pegawai-checkbox" type="checkbox" name="pegawai_ids[]" value="{{ $pegawai->id }}" form="mass-delete-form">
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $pegawai->nama }}</div>
                                    <div class="mt-1"><span class="badge {{ ($pegawai->status_pegawai ?? 'aktif') === 'aktif' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $pegawai->status_label }}</span></div>
                                    @if(($pegawai->status_pegawai ?? 'aktif') !== 'aktif')
                                        <div class="small text-muted">{{ optional($pegawai->nonaktif_sejak)->translatedFormat('d M Y') ?? '-' }}{{ $pegawai->catatan_status ? ' · ' . $pegawai->catatan_status : '' }}</div>
                                    @endif
                                    <div class="small text-muted">NIP: {{ $pegawai->nip }}</div>
                                    <div class="small text-muted">Unit: {{ $pegawai->unitKerja->nama_unit ?? '-' }}</div>
                                </td>
                                <td>
                                    <div>{{ $pegawai->no_hp ?: '-' }}</div>
                                    <div class="small text-muted">Rek: {{ $pegawai->nomor_rekening ?: '-' }}</div>
                                </td>
                                <td>
                                    <div>NPWP: {{ $pegawai->no_npwp ?: '-' }}</div>
                                    <div class="small text-muted">Foto: {{ $pegawai->foto_profil ? 'Sudah ada' : 'Belum ada' }}</div>
                                </td>
                                <td><span class="badge text-bg-light">{{ $pegawai->golongan }}</span></td>
                                <td>{{ $pegawai->jabatan }}</td>
                                <td>{{ $pegawai->agama }}</td>
                                <td>
                                    @if($pegawai->kelasJabatan)
                                        <div class="fw-semibold">Kelas {{ $pegawai->kelasJabatan->nomor_kelas }}</div>
                                        <div class="small text-muted">{{ $pegawai->kelasJabatan->nama_kelas }}</div>
                                    @else
                                        <span class="badge text-bg-warning">Belum dipilih</span>
                                    @endif
                                </td>
                                <td><span class="badge text-bg-light">{{ $pegawai->unitKerja->nama_unit ?? '-' }}</span></td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        <a href="{{ route('pegawai.edit', $pegawai->id) }}" class="btn btn-sm btn-warning btn-icon">
                                            <i class="bi bi-pencil-square"></i><span>Edit</span>
                                        </a>
                                        <form action="{{ route('pegawai.status', $pegawai->id) }}" method="POST" onsubmit="return confirm('{{ ($pegawai->status_pegawai ?? 'aktif') === 'aktif' ? 'Ubah status pegawai ini menjadi mutasi?' : 'Aktifkan kembali pegawai ini?' }}')">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status_pegawai" value="{{ ($pegawai->status_pegawai ?? 'aktif') === 'aktif' ? 'mutasi' : 'aktif' }}">
                                            <button type="submit" class="btn btn-sm {{ ($pegawai->status_pegawai ?? 'aktif') === 'aktif' ? 'btn-outline-secondary' : 'btn-outline-success' }} btn-icon">
                                                <i class="bi {{ ($pegawai->status_pegawai ?? 'aktif') === 'aktif' ? 'bi-person-dash' : 'bi-person-check' }}"></i><span>{{ ($pegawai->status_pegawai ?? 'aktif') === 'aktif' ? 'Mutasi/Nonaktif' : 'Aktifkan' }}</span>
                                            </button>
                                        </form>
                                        <form action="{{ route('pegawai.destroy', $pegawai->id) }}" method="POST" onsubmit="return confirm('Hapus pegawai ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon">
                                                <i class="bi bi-trash"></i><span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">Tidak ada data pegawai yang cocok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($pegawais->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $pegawais->links() }}
            </div>
        @endif
    </div>

<form action="{{ route('pegawai.destroy.massal') }}" method="POST" id="mass-delete-form" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkAll = document.getElementById('check-all');
        const checkboxes = Array.from(document.querySelectorAll('.pegawai-checkbox'));
        const deleteButton = document.getElementById('btn-delete-selected');

        function syncState() {
            const selected = checkboxes.filter(item => item.checked).length;
            deleteButton.disabled = selected === 0;
            if (checkAll) {
                checkAll.checked = selected > 0 && selected === checkboxes.length;
                checkAll.indeterminate = selected > 0 && selected < checkboxes.length;
            }
        }

        if (checkAll) {
            checkAll.addEventListener('change', function () {
                checkboxes.forEach(item => item.checked = checkAll.checked);
                syncState();
            });
        }

        checkboxes.forEach(item => item.addEventListener('change', syncState));
        syncState();
    });
</script>
@endpush
