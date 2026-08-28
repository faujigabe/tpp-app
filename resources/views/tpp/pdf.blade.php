<h3 style="text-align:center;">
PEMERINTAH PROVINSI SUMATERA UTARA<br>
{{ strtoupper($unitKerjaLabel ?? 'SEMUA UNIT KERJA') }}
</h3>
<hr>
<h2 style="text-align:center;">LAPORAN TAMBAHAN PENGHASILAN PEGAWAI (TPP)</h2>

@if($request->bulan && $request->tahun)
@php
    $bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
@endphp
<p style="text-align:center;">Bulan {{ $bulanNama[(int) $request->bulan] ?? $request->bulan }} Tahun {{ $request->tahun }}</p>
@endif

<table width="100%" border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Tambahan TPP</th>
            <th>Potongan TPP</th>
            <th>TPP Kotor</th>
            <th>Pajak</th>
            <th>Zakat</th>
            <th>Total Diterima</th>
        </tr>
    </thead>
    <tbody>
        @forelse($tpps as $tpp)
        <tr>
            <td>{{ $tpp->referensi_nama }}</td>
            <td>{{ number_format($tpp->tambahan_tpp ?? 0,0,',','.') }}</td>
            <td>{{ number_format($tpp->potongan_tpp ?? 0,2,',','.') }}%</td>
            <td>{{ number_format($tpp->tpp_kotor,0,',','.') }}</td>
            <td>{{ number_format($tpp->pajak,0,',','.') }}</td>
            <td>{{ number_format($tpp->zakat,0,',','.') }}</td>
            <td><strong>{{ number_format($tpp->total_diterima,0,',','.') }}</strong></td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="text-align:center;">Tidak ada data TPP untuk periode yang dipilih.</td>
        </tr>
        @endforelse
    </tbody>

    @php
$total_semua = $tpps->sum('total_diterima');
@endphp

@if($tpps->isNotEmpty())
<tr>
    <td colspan="6"><strong>Total Keseluruhan</strong></td>
    <td><strong>{{ number_format($total_semua,0,',','.') }}</strong></td>
</tr>
@endif

</table>

<br><br>
<div style="text-align:right;">
    <p>Medan, {{ date('d-m-Y') }}</p>
    <p>Kepala Biro,</p>
    <br><br><br>
    <p><strong>______________________</strong></p>
</div>
