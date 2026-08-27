@extends('layouts.main')

@section('title', 'Edit Massal TPP')

@section('content')
<h2 class="mb-3">Edit Massal TPP</h2>
<form method="POST" action="{{ route('tpp.update.massal') }}" class="card shadow-soft">
@csrf
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-bordered table-sm align-middle mb-0">
<thead class="table-light text-center">
<tr>
    <th>Pegawai</th>
    <th>Produktivitas</th>
    <th>Kehadiran</th>
    <th>Perilaku</th>
    <th>Tambahan TPP</th>
    <th>Potongan TPP (%)</th>
    <th>BPJS 1%</th>
    <th>BPJS 4%</th>
    <th>Tempat Bertugas</th>
    <th>Tunjangan PPH</th>
    <th style="min-width: 220px;">
    <div class="d-flex flex-column align-items-center gap-1">
        <span>Potongan PPH 21</span>
        <div class="d-flex flex-wrap justify-content-center gap-1">
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnPilihSemuaPajakEditMassal">Pilih Semua</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnKosongkanSemuaPajakEditMassal">Kosongkan Semua</button>
        </div>
    </div>
</th>
    <th>Iuran JKK</th>
    <th>Iuran JKM</th>
    <th>Iuran Tapera</th>
    <th>Iuran Pensiun</th>
    <th>Tunjangan JHT</th>
    <th>Bulog</th>
</tr>
</thead>
<tbody>
@foreach($tpps as $tpp)
<tr>
    <td>{{ $tpp->pegawai->nama }}<div class="small text-muted">{{ $tpp->pegawai->nip }}</div></td>
    <td><input type="number" class="form-control form-control-sm" name="tpp[{{ $tpp->id }}][produktivitas]" value="{{ $tpp->produktivitas }}" step="0.01"></td>
    <td><input type="number" class="form-control form-control-sm" name="tpp[{{ $tpp->id }}][kehadiran]" value="{{ $tpp->kehadiran }}" step="0.01"></td>
    <td><input type="number" class="form-control form-control-sm" name="tpp[{{ $tpp->id }}][perilaku]" value="{{ $tpp->perilaku }}" step="0.01"></td>
    <td><input type="number" class="form-control form-control-sm" name="tpp[{{ $tpp->id }}][tambahan_tpp]" value="{{ $tpp->tambahan_tpp ?? 0 }}" min="0" step="0.01"></td>
    <td><input type="number" class="form-control form-control-sm" name="tpp[{{ $tpp->id }}][potongan_tpp]" value="{{ $tpp->potongan_tpp ?? 0 }}" min="0" max="100" step="0.01"></td>
    <td><input type="number" class="form-control form-control-sm" name="tpp[{{ $tpp->id }}][iuran_wajib]" value="{{ $tpp->iuran_wajib }}" step="0.01"></td>
    <td><input type="number" class="form-control form-control-sm" name="tpp[{{ $tpp->id }}][bpjs_kesehatan_pemberi_kerja]" value="{{ $tpp->bpjs_kesehatan_pemberi_kerja ?? 0 }}" min="0" step="0.01"></td>
    <td><input type="number" class="form-control form-control-sm" name="tpp[{{ $tpp->id }}][tpp_tempat_bertugas]" value="{{ $tpp->tpp_tempat_bertugas ?? 0 }}" min="0" step="0.01"></td>
    <td><input type="number" class="form-control form-control-sm" name="tpp[{{ $tpp->id }}][tunjangan_pph]" value="{{ $tpp->tunjangan_pph ?? 0 }}" min="0" step="0.01"></td>
    <td class="text-center">
        <input type="hidden" name="tpp[{{ $tpp->id }}][hitung_pajak]" value="0">
        <input type="checkbox" class="form-check-input hitung-pajak-item" name="tpp[{{ $tpp->id }}][hitung_pajak]" value="1" @checked((bool) ($tpp->hitung_pajak ?? false))>
    </td>
    <td><input type="number" class="form-control form-control-sm" name="tpp[{{ $tpp->id }}][iuran_jkk]" value="{{ $tpp->iuran_jkk ?? 0 }}" min="0" step="0.01"></td>
    <td><input type="number" class="form-control form-control-sm" name="tpp[{{ $tpp->id }}][iuran_jkm]" value="{{ $tpp->iuran_jkm ?? 0 }}" min="0" step="0.01"></td>
    <td><input type="number" class="form-control form-control-sm" name="tpp[{{ $tpp->id }}][iuran_tapera]" value="{{ $tpp->iuran_tapera ?? 0 }}" min="0" step="0.01"></td>
    <td><input type="number" class="form-control form-control-sm" name="tpp[{{ $tpp->id }}][iuran_pensiun]" value="{{ $tpp->iuran_pensiun ?? 0 }}" min="0" step="0.01"></td>
    <td><input type="number" class="form-control form-control-sm" name="tpp[{{ $tpp->id }}][tunjangan_jht]" value="{{ $tpp->tunjangan_jht ?? 0 }}" min="0" step="0.01"></td>
    <td><input type="number" class="form-control form-control-sm" name="tpp[{{ $tpp->id }}][bulog]" value="{{ $tpp->bulog ?? 0 }}" min="0" step="0.01"></td>
</tr>
@endforeach
</tbody>
</table>
</div>
</div>
<div class="card-footer bg-white"><button type="submit" class="btn btn-primary">Simpan Perubahan</button></div>
</form>
@endsection


@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const items = Array.from(document.querySelectorAll('.hitung-pajak-item'));
    const btnPilihSemua = document.getElementById('btnPilihSemuaPajakEditMassal');
    const btnKosongkanSemua = document.getElementById('btnKosongkanSemuaPajakEditMassal');
    if (!items.length) return;

    btnPilihSemua?.addEventListener('click', () => {
      items.forEach((item) => {
        item.checked = true;
      });
    });

    btnKosongkanSemua?.addEventListener('click', () => {
      items.forEach((item) => {
        item.checked = false;
      });
    });
  });
</script>
@endpush
