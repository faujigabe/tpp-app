@extends('layouts.main')

@section('title', 'Data TPP')

@section('content')

<h2>Edit Massal TPP</h2>

<form method="POST" action="{{ route('tpp.update.massal') }}">
@csrf

<table border="1" cellpadding="5">
<tr>
    <th>Nama</th>
    <th>Produktivitas</th>
    <th>Kehadiran</th>
    <th>Perilaku</th>
    <th>Iuran</th>
</tr>

@foreach($tpps as $tpp)
<tr>
    <td>{{ $tpp->pegawai->nama }}</td>

    <td>
        <input type="number" name="tpp[{{ $tpp->id }}][produktivitas]"
               value="{{ $tpp->produktivitas }}">
    </td>

    <td>
        <input type="number" name="tpp[{{ $tpp->id }}][kehadiran]"
               value="{{ $tpp->kehadiran }}">
    </td>

    <td>
        <input type="number" name="tpp[{{ $tpp->id }}][perilaku]"
               value="{{ $tpp->perilaku }}">
    </td>

    <td>
        <input type="number" name="tpp[{{ $tpp->id }}][iuran_wajib]"
               value="{{ $tpp->iuran_wajib }}">
    </td>
</tr>
@endforeach

</table>

<br>
<button type="submit">Simpan Perubahan</button>
</form>

@endsection