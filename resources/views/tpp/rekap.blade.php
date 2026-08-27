@extends('layouts.main')
@section('title', 'Rekap Perhitungan TPP')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h4 class="mb-0">Rekap Perhitungan TPP</h4>
  <a href="{{ route('tpp.rekap.export', array_filter(['bulan' => $bulan, 'tahun' => $tahun, 'unit_kerja_id' => $selectedUnitKerjaId ?? null])) }}" class="btn btn-success">
    <i class="bi bi-file-earmark-excel"></i> Download Excel Rekap
  </a>
</div>

<form class="row g-2 mb-3" method="GET" action="{{ route('tpp.rekap') }}">
  <div class="col-auto">
    <select name="bulan" class="form-select">
      @foreach(($bulanNama ?? [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember']) as $b=>$nm)
        <option value="{{ $b }}" {{ $bulan==$b?'selected':'' }}>{{ $nm }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-auto">
    <input type="number" name="tahun" class="form-control" value="{{ $tahun }}" min="2000" max="2100">
  </div>
  @if(auth()->user()->isSuperAdmin())
  <div class="col-auto">
    <select name="unit_kerja_id" class="form-select">
      <option value="">Semua Unit Kerja</option>
      @foreach(($availableUnitKerjas ?? collect()) as $unit)
        <option value="{{ $unit->id }}" {{ (int) ($selectedUnitKerjaId ?? 0) === (int) $unit->id ? 'selected' : '' }}>{{ $unit->nama_unit }}</option>
      @endforeach
    </select>
  </div>
  @endif
  <div class="col-auto">
    <button class="btn btn-primary">Tampilkan</button>
  </div>
</form>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-body text-center py-4">
    <div class="fw-bold fs-5">RINCIAN PERHITUNGAN TAMBAHAN PENGHASILAN PEGAWAI (TPP)</div>
    <div class="fw-semibold fs-6">{{ strtoupper($activeUnitKerja?->nama_unit ?? 'SEMUA UNIT KERJA') }}</div>
    <div class="mt-2">Bulan {{ $bulanNama[$bulan] ?? $bulan }} Tahun {{ $tahun }}</div>
  </div>
</div>

<div class="table-responsive" style="max-height: 72vh;">
  <table class="table table-sm table-bordered align-middle table-striped">
    <thead class="table-light text-center" style="position: sticky; top: 0; z-index: 2;">
      <tr>
        <th>No</th>
        <th>Nama</th>
        <th>NIP</th>
        <th>Rekening</th>
        <th>Gol</th>
        <th>Jabatan</th>
        <th>Kelas</th>
        <th>Prod %</th>
        <th>Keh %</th>
        <th>Per %</th>
        <th colspan="4" class="text-center">Beban Kerja</th>
        <th colspan="4" class="text-center">Prestasi Kerja</th>
        <th colspan="4" class="text-center">Kondisi Kerja</th>
        <th colspan="4" class="text-center">Kelangkaan Profesi</th>
        <th>Jumlah Besaran</th>
        <th>TPP Kotor</th>
        <th>BPJS Kesehatan 1%</th>
        <th>BPJS Kesehatan 4%</th>
        <th>TPP Setelah BPJS</th>
        <th>Pajak</th>
        <th>TPP Setelah Potong Pajak</th>
        <th>Zakat</th>
        <th>TPP Diterima</th>
      </tr>
      <tr>
        <th colspan="10"></th>
        <th>PK</th><th>DK</th><th>Per</th><th>Jml</th>
        <th>PK</th><th>DK</th><th>Per</th><th>Jml</th>
        <th>PK</th><th>DK</th><th>Per</th><th>Jml</th>
        <th>PK</th><th>DK</th><th>Per</th><th>Jml</th>
        <th colspan="9"></th>
      </tr>
    </thead>

    <tbody>
      @forelse($rows as $i => $tpp)
        @php
          $prod = (float) $tpp->produktivitas;
          $keh  = (float) $tpp->kehadiran;
          $per  = (float) $tpp->perilaku;
          $calc = \App\Support\TppRekapBuilder::rowFromTpp($tpp);
          $beban = ['pk'=>$calc['beban_pk'], 'dk'=>$calc['beban_dk'], 'pr'=>$calc['beban_pr'], 'jml'=>$calc['beban_jml']];
          $pres  = ['pk'=>$calc['pres_pk'], 'dk'=>$calc['pres_dk'], 'pr'=>$calc['pres_pr'], 'jml'=>$calc['pres_jml']];
          $kond  = ['pk'=>$calc['kond_pk'], 'dk'=>$calc['kond_dk'], 'pr'=>$calc['kond_pr'], 'jml'=>$calc['kond_jml']];
          $lang  = ['pk'=>$calc['lang_pk'], 'dk'=>$calc['lang_dk'], 'pr'=>$calc['lang_pr'], 'jml'=>$calc['lang_jml']];
          $jumlahBesaran = $calc['jumlah_besaran'];
          $tppSetelahBpjs = $calc['setelah_bpjs'];
          $tppSetelahPajak = $calc['setelah_pajak'];
        @endphp
        <tr>
          <td class="text-center">{{ $i + 1 }}</td>
          <td>{{ $tpp->referensi_nama }}</td>
          <td>{{ $tpp->referensi_nip }}</td>
          <td>{{ $tpp->referensi_nomor_rekening ?: '-' }}</td>
          <td class="text-center">{{ $tpp->referensi_golongan }}</td>
          <td>{{ $tpp->referensi_jabatan }}</td>
          <td class="text-center">{{ $tpp->referensi_nomor_kelas ?: '-' }}</td>
          <td class="text-end">{{ number_format($prod,2,',','.') }}</td>
          <td class="text-end">{{ number_format($keh,2,',','.') }}</td>
          <td class="text-end">{{ number_format($per,2,',','.') }}</td>
          <td class="text-end">{{ number_format($beban['pk'],2,',','.') }}</td>
          <td class="text-end">{{ number_format($beban['dk'],2,',','.') }}</td>
          <td class="text-end">{{ number_format($beban['pr'],2,',','.') }}</td>
          <td class="text-end fw-semibold">{{ number_format($beban['jml'],2,',','.') }}</td>
          <td class="text-end">{{ number_format($pres['pk'],2,',','.') }}</td>
          <td class="text-end">{{ number_format($pres['dk'],2,',','.') }}</td>
          <td class="text-end">{{ number_format($pres['pr'],2,',','.') }}</td>
          <td class="text-end fw-semibold">{{ number_format($pres['jml'],2,',','.') }}</td>
          <td class="text-end">{{ number_format($kond['pk'],2,',','.') }}</td>
          <td class="text-end">{{ number_format($kond['dk'],2,',','.') }}</td>
          <td class="text-end">{{ number_format($kond['pr'],2,',','.') }}</td>
          <td class="text-end fw-semibold">{{ number_format($kond['jml'],2,',','.') }}</td>
          <td class="text-end">{{ number_format($lang['pk'],2,',','.') }}</td>
          <td class="text-end">{{ number_format($lang['dk'],2,',','.') }}</td>
          <td class="text-end">{{ number_format($lang['pr'],2,',','.') }}</td>
          <td class="text-end fw-semibold">{{ number_format($lang['jml'],2,',','.') }}</td>
          <td class="text-end">{{ number_format($jumlahBesaran,2,',','.') }}</td>
          <td class="text-end">{{ number_format((float)$tpp->tpp_kotor,2,',','.') }}</td>
          <td class="text-end">{{ number_format((float)$tpp->iuran_wajib,2,',','.') }}</td>
          <td class="text-end">{{ number_format((float)($tpp->bpjs_kesehatan_pemberi_kerja ?? 0),2,',','.') }}</td>
          <td class="text-end">{{ number_format($tppSetelahBpjs,2,',','.') }}</td>
          <td class="text-end">{{ number_format((float)$tpp->pajak,2,',','.') }}</td>
          <td class="text-end">{{ number_format($tppSetelahPajak,2,',','.') }}</td>
          <td class="text-end">{{ number_format((float)$tpp->zakat,2,',','.') }}</td>
          <td class="text-end fw-bold">{{ number_format((float)$tpp->total_diterima,2,',','.') }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="35" class="text-center text-muted py-4">Belum ada data untuk periode ini.</td>
        </tr>
      @endforelse
    </tbody>

    @if($rows->count())
    <tfoot class="table-secondary">
      <tr>
        <th colspan="10" class="text-center">TOTAL</th>
        <th class="text-end">{{ number_format($totals['beban_pk'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['beban_dk'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['beban_pr'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['beban_jml'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['pres_pk'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['pres_dk'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['pres_pr'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['pres_jml'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['kond_pk'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['kond_dk'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['kond_pr'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['kond_jml'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['lang_pk'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['lang_dk'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['lang_pr'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['lang_jml'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['jumlah_besaran'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['tpp_kotor'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['bpjs1'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['bpjs4'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['setelah_bpjs'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['pajak'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['setelah_pajak'],2,',','.') }}</th>
        <th class="text-end">{{ number_format($totals['zakat'],2,',','.') }}</th>
        <th class="text-end fw-bold">{{ number_format($totals['diterima'],2,',','.') }}</th>
      </tr>
    </tfoot>
    @endif
  </table>
</div>
@endsection
