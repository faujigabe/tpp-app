@extends('layouts.main')
@section('title', 'Rekap Perhitungan TPP')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Rekap Perhitungan TPP</h4>
</div>

<form class="row g-2 mb-3" method="GET" action="{{ route('tpp.rekap') }}">
  <div class="col-auto">
    <select name="bulan" class="form-select">
      @php $bulanNama=[1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember']; @endphp
      @foreach($bulanNama as $b=>$nm)
        <option value="{{ $b }}" {{ $bulan==$b?'selected':'' }}>{{ $nm }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-auto">
    <input type="number" name="tahun" class="form-control" value="{{ $tahun }}" min="2000" max="2100">
  </div>
  <div class="col-auto">
    <button class="btn btn-primary">Tampilkan</button>
  </div>
</form>

<div class="table-responsive" style="max-height: 70vh;">
  <table class="table table-sm table-bordered align-middle">
    <thead class="table-light" style="position: sticky; top: 0; z-index: 2;">
      <tr>
        <th>No</th>
        <th>Nama</th>
        <th>NIP</th>
        <th>No HP</th>
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
        <th>BPJS</th>
        <th>TPP Setelah BPJS</th>
        <th>Pajak</th>
        <th>Zakat</th>
        <th>TPP Diterima</th>
      </tr>
      <tr>
        <th colspan="11"></th>

        <th>PK</th><th>DK</th><th>Per</th><th>Jml</th>
        <th>PK</th><th>DK</th><th>Per</th><th>Jml</th>
        <th>PK</th><th>DK</th><th>Per</th><th>Jml</th>
        <th>PK</th><th>DK</th><th>Per</th><th>Jml</th>

        <th colspan="7"></th>
      </tr>
    </thead>

    <tbody>
      @foreach($rows as $i => $tpp)
        @php
          $p = $tpp->pegawai;
          $k = $p->kelasJabatan;

          $prod = (float)$tpp->produktivitas;
          $keh  = (float)$tpp->kehadiran;
          $per  = (float)$tpp->perilaku;

          // helper breakdown untuk 1 komponen nilai X
          $break = function($x) use ($prod,$keh,$per) {
            $x = (float)$x;
            $basePK = 0.40 * $x;
            $baseDK = 0.18 * $x;
            $basePR = 0.42 * $x;

            $valPK = ($prod/100) * $basePK;
            $valDK = ($keh/100)  * $baseDK;
            $valPR = ($per/100)  * $basePR;

            return [
              'pk'=>$valPK, 'dk'=>$valDK, 'pr'=>$valPR,
              'jml'=>($valPK+$valDK+$valPR)
            ];
          };

          $beban = $break($k->beban_kerja);
          $pres  = $break($k->prestasi_kerja);
          $kond  = $break($k->kondisi_kerja);
          $lang  = $break($k->kelangkaan_profesi);

          $jumlahBesaran = (float)$k->beban_kerja + (float)$k->prestasi_kerja + (float)$k->kondisi_kerja + (float)$k->kelangkaan_profesi;

          $tppSetelahBpjs = (float)$tpp->tpp_kotor - (float)$tpp->iuran_wajib;
        @endphp

        <tr>
          <td>{{ $i+1 }}</td>
          <td>{{ $p->nama }}</td>
          <td>{{ $p->nip }}</td>
          <td>{{ $p->no_hp ?? '-' }}</td>
          <td>{{ $p->nomor_rekening ?? '-' }}</td>
          <td>{{ $p->golongan }}</td>
          <td>{{ $p->jabatan }}</td>
          <td>{{ $k->nomor_kelas ?? '-' }}</td>

          <td>{{ number_format($prod,2,',','.') }}</td>
          <td>{{ number_format($keh,2,',','.') }}</td>
          <td>{{ number_format($per,2,',','.') }}</td>

          <td>{{ number_format($beban['pk'],2,',','.') }}</td>
          <td>{{ number_format($beban['dk'],2,',','.') }}</td>
          <td>{{ number_format($beban['pr'],2,',','.') }}</td>
          <td><strong>{{ number_format($beban['jml'],2,',','.') }}</strong></td>

          <td>{{ number_format($pres['pk'],2,',','.') }}</td>
          <td>{{ number_format($pres['dk'],2,',','.') }}</td>
          <td>{{ number_format($pres['pr'],2,',','.') }}</td>
          <td><strong>{{ number_format($pres['jml'],2,',','.') }}</strong></td>

          <td>{{ number_format($kond['pk'],2,',','.') }}</td>
          <td>{{ number_format($kond['dk'],2,',','.') }}</td>
          <td>{{ number_format($kond['pr'],2,',','.') }}</td>
          <td><strong>{{ number_format($kond['jml'],2,',','.') }}</strong></td>

          <td>{{ number_format($lang['pk'],2,',','.') }}</td>
          <td>{{ number_format($lang['dk'],2,',','.') }}</td>
          <td>{{ number_format($lang['pr'],2,',','.') }}</td>
          <td><strong>{{ number_format($lang['jml'],2,',','.') }}</strong></td>

          <td>{{ number_format($jumlahBesaran,2,',','.') }}</td>
          <td>{{ number_format((float)$tpp->tpp_kotor,2,',','.') }}</td>
          <td>{{ number_format((float)$tpp->iuran_wajib,2,',','.') }}</td>
          <td>{{ number_format($tppSetelahBpjs,2,',','.') }}</td>
          <td>{{ number_format((float)$tpp->pajak,2,',','.') }}</td>
          <td>{{ number_format((float)$tpp->zakat,2,',','.') }}</td>
          <td><strong>{{ number_format((float)$tpp->total_diterima,2,',','.') }}</strong></td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection