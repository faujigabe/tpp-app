@extends('layouts.main')

@section('title', 'Data Pegawai')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-0">Data Pegawai</h3>
        <div class="text-muted">Kelola data pegawai, import Excel, dan hapus massal.</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('pegawai.import.form') }}" class="btn btn-outline-success btn-icon">
            <i class="bi bi-upload"></i> Import Pegawai
        </a>
        <a href="{{ route('pegawai.create') }}" class="btn btn-primary btn-icon">
            <i class="bi bi-plus-circle"></i> Tambah Pegawai
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger shadow-soft">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="formHapusMassalPegawai" action="{{ route('pegawai.destroy.massal') }}" method="POST">
    @csrf
    @method('DELETE')

    <div class="card shadow-soft">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="fw-semibold">Daftar Pegawai</div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-outline-danger btn-icon" id="btnHapusMassalPegawai" disabled onclick="return confirm('Yakin ingin menghapus semua pegawai yang dipilih?')">
                    <i class="bi bi-trash3"></i> Hapus Massal
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 52px;" class="text-center">
                                <input type="checkbox" class="form-check-input" id="checkAllPegawai">
                            </th>
                            <th style="min-width: 240px;">Pegawai</th>
                            <th>NIP</th>
                            <th>No HP</th>
                            <th>Nomor Rekening</th>
                            <th>Golongan</th>
                            <th>Jabatan</th>
                            <th>Kelas</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pegawais as $pegawai)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="pegawai_ids[]" value="{{ $pegawai->id }}" class="form-check-input pegawai-check">
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $pegawai->nama }}</div>
                                    <div class="small text-muted">{{ $pegawai->agama }}</div>
                                </td>
                                <td>{{ $pegawai->nip }}</td>
                                <td>{{ $pegawai->no_hp ?: '-' }}</td>
                                <td>{{ $pegawai->nomor_rekening ?: '-' }}</td>
                                <td>{{ $pegawai->golongan }}</td>
                                <td>{{ $pegawai->jabatan }}</td>
                                <td>
                                    @if($pegawai->kelasJabatan)
                                        {{ $pegawai->kelasJabatan->nomor_kelas ?? '-' }} - {{ $pegawai->kelasJabatan->nama_kelas ?? '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('pegawai.edit', $pegawai->id) }}" class="btn btn-sm btn-warning btn-icon">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <form action="{{ route('pegawai.destroy', $pegawai->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pegawai ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger btn-icon"><i class="bi bi-trash"></i> Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">Belum ada data pegawai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">{{ $pegawais->links() }}</div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('checkAllPegawai');
    const checks = Array.from(document.querySelectorAll('.pegawai-check'));
    const submitButton = document.getElementById('btnHapusMassalPegawai');

    function syncButton() {
        const checkedCount = checks.filter(item => item.checked).length;
        submitButton.disabled = checkedCount === 0;
        if (checkAll) {
            checkAll.checked = checks.length > 0 && checkedCount === checks.length;
            checkAll.indeterminate = checkedCount > 0 && checkedCount < checks.length;
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            checks.forEach(item => item.checked = checkAll.checked);
            syncButton();
        });
    }

    checks.forEach(item => item.addEventListener('change', syncButton));
    syncButton();
});
</script>
@endpush
