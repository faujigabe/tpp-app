@extends('layouts.main')

@section('title', 'Data TPP')

@section('content')
@php
    $bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    $rupiah = fn($angka, $dec = 2) => 'Rp ' . number_format((float) $angka, $dec, ',', '.');
    $periodeLabel = ($bulanNama[(int) $bulan] ?? $bulan) . '/' . $tahun;
@endphp

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-0">Daftar Hasil Perhitungan TPP</h3>
        <div class="text-muted">Kelola data TPP per bulan, hapus massal, cetak PDF, dan export Excel.</div>
    </div>

    @auth
        @if(in_array(auth()->user()->role, ['admin', 'operator']))
            <a href="{{ route('tpp.create') }}" class="btn btn-primary btn-icon">
                <i class="bi bi-plus-circle"></i> Tambah Perhitungan
            </a>
        @endif
    @endauth
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

<div class="card shadow-soft mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('tpp.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Bulan</label>
                <select name="bulan" class="form-select">
                    @foreach($bulanNama as $key => $label)
                        <option value="{{ $key }}" {{ (int) $bulan === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tahun</label>
                <input type="number" name="tahun" class="form-control" min="2000" max="2100" value="{{ $tahun }}">
            </div>
            <div class="col-md-7 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-secondary btn-icon">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="{{ route('tpp.index') }}" class="btn btn-outline-secondary btn-icon">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
                <div class="ms-auto d-flex gap-2 flex-wrap justify-content-end">
                    @auth
                        @if(in_array(auth()->user()->role, ['admin', 'operator']) && $jumlahDataFilter > 0)
                            <button type="button" class="btn btn-outline-danger btn-icon" data-bs-toggle="modal" data-bs-target="#hapusMassalModal">
                                <i class="bi bi-trash3"></i> Hapus Massal
                            </button>
                        @endif
                    @endauth
                    <a class="btn btn-outline-danger btn-icon" href="{{ route('tpp.cetak', ['bulan' => $bulan, 'tahun' => $tahun]) }}">
                        <i class="bi bi-printer"></i> Cetak PDF
                    </a>
                    <a class="btn btn-outline-success btn-icon" href="{{ route('tpp.export', ['bulan' => $bulan, 'tahun' => $tahun]) }}">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-soft">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="fw-semibold">Data TPP Periode {{ $periodeLabel }}</div>
        <span class="badge text-bg-light border">{{ number_format($jumlahDataFilter, 0, ',', '.') }} data</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 260px;">Pegawai</th>
                        <th class="text-center" style="width: 130px;">Periode</th>
                        <th class="text-end">TPP Kotor</th>
                        <th class="text-end">Pajak</th>
                        <th class="text-end">Zakat</th>
                        <th class="text-end">Total Diterima</th>
                        <th class="text-center" style="width: 170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tpps as $tpp)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $tpp->pegawai->nama ?? '-' }}</div>
                                <div class="small text-muted">NIP: {{ $tpp->pegawai->nip ?? '-' }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill text-bg-light border">{{ $bulanNama[(int) $tpp->bulan] ?? $tpp->bulan }}/{{ $tpp->tahun }}</span>
                            </td>
                            <td class="text-end">{{ $rupiah($tpp->tpp_kotor) }}</td>
                            <td class="text-end">{{ $rupiah($tpp->pajak) }}</td>
                            <td class="text-end">{{ $rupiah($tpp->zakat) }}</td>
                            <td class="text-end fw-bold">{{ $rupiah($tpp->total_diterima) }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    @if(Route::has('tpp.edit') && auth()->check() && auth()->user()->role === 'admin')
                                        <a href="{{ route('tpp.edit', $tpp->id) }}" class="btn btn-sm btn-warning btn-icon">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                    @endif
                                    @auth
                                        @if(in_array(auth()->user()->role, ['admin', 'operator']))
                                            <form action="{{ route('tpp.destroy', $tpp->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data TPP ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger btn-icon">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </form>
                                        @endif
                                    @endauth
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">Tidak ada data TPP untuk periode {{ $periodeLabel }}.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($tpps, 'links'))
        <div class="card-footer bg-white">{{ $tpps->links() }}</div>
    @endif
</div>

@auth
    @if(in_array(auth()->user()->role, ['admin', 'operator']) && $jumlahDataFilter > 0)
        <div class="modal fade" id="hapusMassalModal" tabindex="-1" aria-labelledby="hapusMassalModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form id="formHapusMassal" method="POST" action="{{ route('tpp.destroy.massal') }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="bulan" value="{{ $bulan }}">
                        <input type="hidden" name="tahun" value="{{ $tahun }}">
                        <input type="hidden" name="konfirmasi_hapus" id="konfirmasi_hapus" value="">

                        <div class="modal-header">
                            <h5 class="modal-title" id="hapusMassalModalLabel">Konfirmasi Hapus Massal</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger d-flex align-items-start gap-2 mb-3">
                                <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
                                <div>
                                    <div class="fw-semibold">Tindakan ini permanen dan tidak bisa dibatalkan.</div>
                                    <div class="small">Pastikan periode yang dipilih sudah benar sebelum melanjutkan.</div>
                                </div>
                            </div>

                            <div class="border rounded p-3 bg-light mb-3">
                                <div class="small text-muted mb-2">Ringkasan penghapusan</div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Periode</span>
                                    <strong>{{ $periodeLabel }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Jumlah data yang akan dihapus</span>
                                    <strong class="text-danger">{{ number_format($jumlahDataFilter, 0, ',', '.') }} data</strong>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="konfirmasiHapusInput" class="form-label fw-semibold">Ketik HAPUS</label>
                                <input type="text" class="form-control" id="konfirmasiHapusInput" placeholder="HAPUS" autocomplete="off">
                                <div class="form-text">Huruf besar atau kecil tetap diterima.</div>
                            </div>

                            <div>
                                <label for="passwordKonfirmasiInput" class="form-label fw-semibold">Masukkan password akun Anda</label>
                                <input type="password" class="form-control" id="passwordKonfirmasiInput" name="password_konfirmasi" placeholder="Password akun" autocomplete="current-password">
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <div class="small text-muted">Periode: <strong>{{ $periodeLabel }}</strong></div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger" id="btnKonfirmasiHapusMassal" disabled>
                                    <i class="bi bi-trash3 me-1"></i> Ya, Hapus {{ number_format($jumlahDataFilter, 0, ',', '.') }} Data
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endauth
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputKonfirmasi = document.getElementById('konfirmasiHapusInput');
    const hiddenKonfirmasi = document.getElementById('konfirmasi_hapus');
    const inputPassword = document.getElementById('passwordKonfirmasiInput');
    const tombolSubmit = document.getElementById('btnKonfirmasiHapusMassal');
    const modalElement = document.getElementById('hapusMassalModal');

    if (!inputKonfirmasi || !hiddenKonfirmasi || !inputPassword || !tombolSubmit || !modalElement) {
        return;
    }

    function sinkronkan() {
        const konfirmasiBenar = inputKonfirmasi.value.trim().toUpperCase() === 'HAPUS';
        const passwordTerisi = inputPassword.value.trim() !== '';
        hiddenKonfirmasi.value = inputKonfirmasi.value.trim().toUpperCase();
        tombolSubmit.disabled = !(konfirmasiBenar && passwordTerisi);
    }

    inputKonfirmasi.addEventListener('input', sinkronkan);
    inputPassword.addEventListener('input', sinkronkan);

    modalElement.addEventListener('hidden.bs.modal', function () {
        inputKonfirmasi.value = '';
        inputPassword.value = '';
        hiddenKonfirmasi.value = '';
        tombolSubmit.disabled = true;
    });
});
</script>
@endpush
