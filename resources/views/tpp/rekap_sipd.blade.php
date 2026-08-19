@extends('layouts.main')

@section('title', 'Rekap SIPD')

@push('styles')
<style>
    .rekap-sipd-wrap {
        max-height: 72vh;
        max-width: 100%;
        overflow: auto;
        position: relative;
        border-top: 1px solid #e5e7eb;
        background: #fff;
        scrollbar-gutter: stable;
    }
    .rekap-sipd-wrap::-webkit-scrollbar {
        height: 10px;
        width: 10px;
    }
    .rekap-sipd-wrap::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }
    .rekap-sipd-wrap::-webkit-scrollbar-track {
        background: #f8fafc;
    }
    .rekap-sipd-table {
        width: max-content;
        min-width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: auto;
        font-size: 12px;
        line-height: 1.35;
    }
    .rekap-sipd-table th,
    .rekap-sipd-table td {
        padding: .55rem .5rem;
        vertical-align: middle;
        border-color: #e5e7eb;
        background-clip: padding-box;
    }
    .rekap-sipd-table thead th {
        position: sticky;
        top: 0;
        z-index: 20;
        background: #f8fafc;
        text-align: center;
        vertical-align: middle;
        white-space: normal;
        font-weight: 700;
        box-shadow: inset 0 -1px 0 #d1d5db;
    }
    .rekap-sipd-table tbody td {
        background: #fff;
    }
    .rekap-sipd-table tbody tr:nth-child(even) td {
        background: #f8fafc;
    }
    .rekap-sipd-table tfoot td {
        background: #fff8db;
        font-weight: 700;
        box-shadow: inset 0 1px 0 #e5e7eb;
    }
    .rekap-sipd-table .sticky-no,
    .rekap-sipd-table .sticky-nama {
        position: sticky;
    }
    .rekap-sipd-table th.sticky-no,
    .rekap-sipd-table td.sticky-no {
        left: 0;
        min-width: 56px;
        width: 56px;
        max-width: 56px;
        text-align: center;
        z-index: 25;
        box-shadow: inset -1px 0 0 #d1d5db;
    }
    .rekap-sipd-table th.sticky-nama,
    .rekap-sipd-table td.sticky-nama {
        left: 56px;
        min-width: 230px;
        width: 230px;
        max-width: 230px;
        z-index: 24;
        box-shadow: inset -1px 0 0 #d1d5db;
    }
    .rekap-sipd-table thead th.sticky-no,
    .rekap-sipd-table thead th.sticky-nama {
        background: #f8fafc;
        z-index: 30;
    }
    .rekap-sipd-table tbody td.sticky-no,
    .rekap-sipd-table tbody td.sticky-nama {
        background: #fff;
    }
    .rekap-sipd-table tbody tr:nth-child(even) td.sticky-no,
    .rekap-sipd-table tbody tr:nth-child(even) td.sticky-nama {
        background: #f8fafc;
    }
    .rekap-sipd-table tfoot td.sticky-no,
    .rekap-sipd-table tfoot td.sticky-nama {
        background: #fff8db;
        z-index: 26;
    }
    .rekap-sipd-table .col-name,
    .rekap-sipd-table .col-address,
    .rekap-sipd-table .col-job {
        white-space: normal;
        line-height: 1.4;
        word-break: break-word;
    }
    .rekap-sipd-table .col-name { font-weight: 600; }
    .rekap-sipd-table .col-address { min-width: 220px; }
    .rekap-sipd-table .col-job { min-width: 180px; }
    .rekap-sipd-table .col-short { min-width: 96px; text-align: center; }
    .rekap-sipd-table .col-id { min-width: 135px; white-space: nowrap; }
    .rekap-sipd-table .col-bank { min-width: 110px; white-space: normal; }
    .rekap-sipd-table .col-money { min-width: 120px; white-space: nowrap; text-align: right; }
    .rekap-sipd-summary {
        font-size: 13px;
        color: #64748b;
    }
    @media (max-width: 991.98px) {
        .rekap-sipd-wrap {
            max-height: 68vh;
        }
        .rekap-sipd-table {
            font-size: 11.5px;
        }
        .rekap-sipd-table th.sticky-nama,
        .rekap-sipd-table td.sticky-nama {
            min-width: 200px;
            width: 200px;
            max-width: 200px;
        }
    }
</style>
@endpush

@section('content')
@php
    $bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    $money = fn($value) => number_format((float) $value, 2, ',', '.');
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h3 class="mb-1">Rekap SIPD</h3>
        <p class="text-muted mb-0">Rekap data SIPD berdasarkan periode yang dipilih.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('tpp.rekap.sipd.export', request()->all()) }}" class="btn btn-success btn-icon">
            <i class="bi bi-file-earmark-excel"></i> Download Rekap SIPD
        </a>
    </div>
</div>

<div class="card shadow-soft border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('tpp.rekap.sipd') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Bulan</label>
                <select name="bulan" class="form-select">
                    @foreach($bulanNama as $nomor => $nama)
                        <option value="{{ $nomor }}" @selected((int) request('bulan', $bulan) === $nomor)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tahun</label>
                <input type="number" name="tahun" class="form-control" value="{{ request('tahun', $tahun) }}" min="2000" max="2100">
            </div>
            @if(auth()->user()->isSuperAdmin())
            <div class="col-md-3">
                <label class="form-label">Unit Kerja</label>
                <select name="unit_kerja_id" class="form-select">
                    <option value="">Semua Unit Kerja</option>
                    @foreach(($availableUnitKerjas ?? collect()) as $unit)
                        <option value="{{ $unit->id }}" @selected((int) ($selectedUnitKerjaId ?? 0) === (int) $unit->id)>{{ $unit->nama_unit }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-6 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary btn-icon"><i class="bi bi-funnel"></i> Tampilkan</button>
                <a href="{{ route('tpp.rekap.sipd') }}" class="btn btn-outline-secondary btn-icon"><i class="bi bi-arrow-clockwise"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-soft border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="fw-semibold">Periode {{ $bulanNama[$bulan] ?? $bulan }}/{{ $tahun }}</div>
            <div class="small text-muted">Unit kerja: {{ $activeUnitKerja?->nama_unit ?? 'Semua Unit Kerja' }}</div>
            <div class="rekap-sipd-summary">Scroll ke kanan untuk melihat seluruh kolom. Header, No., dan Nama Pegawai tetap terlihat.</div>
        </div>
        <div class="small text-muted">{{ count($rows) }} data</div>
    </div>
    <div class="card-body p-0">
        <div class="rekap-sipd-wrap">
            <table class="table table-bordered table-hover align-middle mb-0 rekap-sipd-table">
                <thead>
                    <tr>
                        <th class="sticky-no">No.</th>
                        <th class="sticky-nama">Nama Pegawai</th>
                        <th class="col-id">NIP Pegawai</th>
                        <th class="col-id">NIK Pegawai</th>
                        <th class="col-short">Tanggal Lahir Pegawai</th>
                        <th class="col-short">Tipe Jabatan</th>
                        <th class="col-job">Nama Jabatan</th>
                        <th class="col-short">Eselon</th>
                        <th class="col-short">Status ASN</th>
                        <th class="col-short">Golongan</th>
                        <th class="col-short">Masa Kerja Golongan</th>
                        <th class="col-address">Alamat</th>
                        <th class="col-short">Kode Bank</th>
                        <th class="col-bank">Nama Bank</th>
                        <th class="col-id">Nomor Rekening Pegawai</th>
                        <th class="col-money">TPP Beban Kerja</th>
                        <th class="col-money">TPP Tempat Bertugas</th>
                        <th class="col-money">TPP Kondisi Kerja</th>
                        <th class="col-money">TPP Kelangkaan Profesi</th>
                        <th class="col-money">TPP Prestasi Kerja</th>
                        <th class="col-money">Tunjangan PPH</th>
                        <th class="col-money">Iuran Pemberi Kerja</th>
                        <th class="col-money">Iuran Jaminan Kecelakaan Kerja</th>
                        <th class="col-money">Iuran Jaminan Kematian</th>
                        <th class="col-money">Iuran Simpanan Tapera</th>
                        <th class="col-money">Iuran Pensiun</th>
                        <th class="col-money">Tunjangan Jaminan Hari Tua</th>
                        <th class="col-money">BPJS Kesehatan 1% (Peserta)</th>
                        <th class="col-money">Potongan PPh 21</th>
                        <th class="col-money">Zakat</th>
                        <th class="col-money">Bulog</th>
                        <th class="col-money">Jumlah TPP</th>
                        <th class="col-money">Jumlah Potongan</th>
                        <th class="col-money">Jumlah Di Transfer</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="sticky-no text-center">{{ $row['no'] }}</td>
                            <td class="sticky-nama col-name">{{ $row['nama_pegawai'] }}</td>
                            <td class="col-id">{{ $row['nip_pegawai'] }}</td>
                            <td class="col-id">{{ $row['nik_pegawai'] }}</td>
                            <td class="col-short">{{ $row['tanggal_lahir_pegawai'] }}</td>
                            <td class="col-short">{{ $row['tipe_jabatan'] }}</td>
                            <td class="col-job">{{ $row['nama_jabatan'] }}</td>
                            <td class="col-short">{{ $row['eselon'] }}</td>
                            <td class="col-short">{{ $row['status_asn'] }}</td>
                            <td class="col-short">{{ $row['golongan'] }}</td>
                            <td class="col-short">{{ $row['masa_kerja_golongan'] }}</td>
                            <td class="col-address">{{ $row['alamat'] }}</td>
                            <td class="col-short">{{ $row['kode_bank'] }}</td>
                            <td class="col-bank">{{ $row['nama_bank'] }}</td>
                            <td class="col-id">{{ $row['nomor_rekening_pegawai'] }}</td>
                            <td class="col-money">{{ $money($row['tpp_beban_kerja']) }}</td>
                            <td class="col-money">{{ $money($row['tpp_tempat_bertugas']) }}</td>
                            <td class="col-money">{{ $money($row['tpp_kondisi_kerja']) }}</td>
                            <td class="col-money">{{ $money($row['tpp_kelangkaan_profesi']) }}</td>
                            <td class="col-money">{{ $money($row['tpp_prestasi_kerja']) }}</td>
                            <td class="col-money">{{ $money($row['tunjangan_pph']) }}</td>
                            <td class="col-money">{{ $money($row['iuran_pemberi_kerja']) }}</td>
                            <td class="col-money">{{ $money($row['iuran_jaminan_kecelakaan_kerja']) }}</td>
                            <td class="col-money">{{ $money($row['iuran_jaminan_kematian']) }}</td>
                            <td class="col-money">{{ $money($row['iuran_simpanan_tapera']) }}</td>
                            <td class="col-money">{{ $money($row['iuran_pensiun']) }}</td>
                            <td class="col-money">{{ $money($row['tunjangan_jaminan_hari_tua']) }}</td>
                            <td class="col-money">{{ $money($row['potongan_iwp']) }}</td>
                            <td class="col-money">{{ $money($row['potongan_pph_21']) }}</td>
                            <td class="col-money">{{ $money($row['zakat']) }}</td>
                            <td class="col-money">{{ $money($row['bulog']) }}</td>
                            <td class="col-money fw-semibold">{{ $money($row['jumlah_tpp']) }}</td>
                            <td class="col-money fw-semibold text-danger">{{ $money($row['jumlah_potongan']) }}</td>
                            <td class="col-money fw-bold text-success">{{ $money($row['jumlah_di_transfer']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="34" class="text-center py-4 text-muted">Belum ada data untuk periode ini.</td></tr>
                    @endforelse
                </tbody>
                @if(count($rows))
                <tfoot>
                    <tr>
                        <td class="sticky-no text-center">TOTAL</td>
                        <td class="sticky-nama text-center">Akumulasi Seluruh Pegawai</td>
                        <td colspan="13"></td>
                        <td class="col-money">{{ $money($totals['tpp_beban_kerja']) }}</td>
                        <td class="col-money">{{ $money($totals['tpp_tempat_bertugas']) }}</td>
                        <td class="col-money">{{ $money($totals['tpp_kondisi_kerja']) }}</td>
                        <td class="col-money">{{ $money($totals['tpp_kelangkaan_profesi']) }}</td>
                        <td class="col-money">{{ $money($totals['tpp_prestasi_kerja']) }}</td>
                        <td class="col-money">{{ $money($totals['tunjangan_pph']) }}</td>
                        <td class="col-money">{{ $money($totals['iuran_pemberi_kerja']) }}</td>
                        <td class="col-money">{{ $money($totals['iuran_jaminan_kecelakaan_kerja']) }}</td>
                        <td class="col-money">{{ $money($totals['iuran_jaminan_kematian']) }}</td>
                        <td class="col-money">{{ $money($totals['iuran_simpanan_tapera']) }}</td>
                        <td class="col-money">{{ $money($totals['iuran_pensiun']) }}</td>
                        <td class="col-money">{{ $money($totals['tunjangan_jaminan_hari_tua']) }}</td>
                        <td class="col-money">{{ $money($totals['potongan_iwp']) }}</td>
                        <td class="col-money">{{ $money($totals['potongan_pph_21']) }}</td>
                        <td class="col-money">{{ $money($totals['zakat']) }}</td>
                        <td class="col-money">{{ $money($totals['bulog']) }}</td>
                        <td class="col-money fw-semibold">{{ $money($totals['jumlah_tpp']) }}</td>
                        <td class="col-money fw-semibold text-danger">{{ $money($totals['jumlah_potongan']) }}</td>
                        <td class="col-money fw-bold text-success">{{ $money($totals['jumlah_di_transfer']) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
