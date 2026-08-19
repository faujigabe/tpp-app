<h3 style="text-align:center;">
PEMERINTAH PROVINSI SUMATERA UTARA<br>
BIRO ADMINISTRASI PEMBANGUNAN
</h3>
<hr>
<h2 style="text-align:center;">LAPORAN TAMBAHAN PENGHASILAN PEGAWAI (TPP)</h2>

<h2 style="text-align:center;">LAPORAN TPP</h2>

@if($request->bulan && $request->tahun)
<p>Bulan: {{ $request->bulan }} / {{ $request->tahun }}</p>
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
        @foreach($tpps as $tpp)
        <tr>
            <td>{{ $tpp->referensi_nama }}</td>
            <td>{{ number_format($tpp->tambahan_tpp ?? 0,0,',','.') }}</td>
            <td>{{ number_format($tpp->potongan_tpp ?? 0,2,',','.') }}%</td>
            <td>{{ number_format($tpp->tpp_kotor,0,',','.') }}</td>
            <td>{{ number_format($tpp->pajak,0,',','.') }}</td>
            <td>{{ number_format($tpp->zakat,0,',','.') }}</td>
            <td><strong>{{ number_format($tpp->total_diterima,0,',','.') }}</strong></td>
        </tr>
        @endforeach
    </tbody>

    @php
$total_semua = $tpps->sum('total_diterima');
@endphp

<tr>
    <td colspan="6"><strong>Total Keseluruhan</strong></td>
    <td><strong>{{ number_format($total_semua,0,',','.') }}</strong></td>
</tr>

</table>

<br><br>
<div style="text-align:right;">
    <p>Medan, {{ date('d-m-Y') }}</p>
    <p>Kepala Biro,</p>
    <br><br><br>
    <p><strong>______________________</strong></p>
</div>
