@extends('layouts.main')

@section('title', 'Tambah Pegawai')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h3 class="mb-1">Tambah Pegawai</h3>
        <p class="text-muted mb-0">Lengkapi data master pegawai untuk kebutuhan TPP dan Rekap SIPD.</p>
    </div>
    <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary btn-icon">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger shadow-soft border-0">
        <div class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle me-1"></i> Periksa kembali data pegawai.</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('pegawai.store') }}" method="POST" enctype="multipart/form-data" class="card shadow-soft border-0">
    @csrf
    <div class="card-body p-4 p-lg-5">
        <div class="row g-4">
            <div class="col-12"><h5 class="mb-0">Identitas Pegawai</h5></div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Pegawai</label>
                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">NIP</label>
                <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip') }}" required>
                @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">NIK</label>
                <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik') }}">
                @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">No NPWP</label>
                <input type="text" name="no_npwp" class="form-control @error('no_npwp') is-invalid @enderror" value="{{ old('no_npwp') }}">
                @error('no_npwp')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir') }}">
                @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Golongan</label>
                <select name="golongan" class="form-select @error('golongan') is-invalid @enderror" required>
                    <option value="">Pilih golongan</option>
                    @foreach(['II/A','II/B','II/C','II/D','III/A','III/B','III/C','III/D','IV/A','IV/B','IV/C','IV/D','IV/E'] as $gol)
                        <option value="{{ $gol }}" @selected(old('golongan') === $gol)>{{ $gol }}</option>
                    @endforeach
                </select>
                @error('golongan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Agama</label>
                <input type="text" name="agama" class="form-control @error('agama') is-invalid @enderror" value="{{ old('agama') }}" required>
                @error('agama')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">No. HP</label>
                <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp') }}" required>
                @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 pt-2"><h5 class="mb-0">Jabatan dan Status</h5></div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Jabatan</label>
                <textarea name="jabatan" rows="2" class="form-control @error('jabatan') is-invalid @enderror" required>{{ old('jabatan') }}</textarea>
                @error('jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Jabatan</label>
                <select name="nama_jabatan" id="nama_jabatan" class="form-select @error('nama_jabatan') is-invalid @enderror">
                    <option value="">Pilih nama jabatan</option>
                    @foreach($namaJabatanOptions as $option)
                        <option value="{{ $option['value'] }}" data-kelas-id="{{ $option['kelas_jabatan_id'] }}" data-nomor-label="{{ $option['nomor_kelas_label'] }}" data-display-label="{{ $option['label'] }}" @selected(old('nama_jabatan') === $option['value'] && (string) old('kelas_jabatan_id') === (string) $option['kelas_jabatan_id'])>{{ $option['label'] }}</option>
                    @endforeach
                </select>
                <div class="form-text">Yang tersimpan hanya teks nama jabatannya.</div>
                @error('nama_jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tipe Jabatan</label>
                <input type="number" name="tipe_jabatan" class="form-control @error('tipe_jabatan') is-invalid @enderror" value="{{ old('tipe_jabatan') }}" min="0">
                @error('tipe_jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Eselon</label>
                <input type="text" name="eselon" class="form-control @error('eselon') is-invalid @enderror" value="{{ old('eselon') }}">
                @error('eselon')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Status ASN</label>
                <input type="number" name="status_asn" class="form-control @error('status_asn') is-invalid @enderror" value="{{ old('status_asn') }}" min="0">
                @error('status_asn')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Masa Kerja Golongan</label>
                <input type="number" name="masa_kerja_golongan" class="form-control @error('masa_kerja_golongan') is-invalid @enderror" value="{{ old('masa_kerja_golongan') }}" min="0">
                @error('masa_kerja_golongan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Status Pegawai</label>
                <select name="status_pegawai" class="form-select @error('status_pegawai') is-invalid @enderror">
                    <option value="aktif" @selected(old('status_pegawai', 'aktif') === 'aktif')>Aktif</option>
                    <option value="mutasi" @selected(old('status_pegawai') === 'mutasi')>Mutasi</option>
                    <option value="pensiun" @selected(old('status_pegawai') === 'pensiun')>Pensiun</option>
                    <option value="keluar" @selected(old('status_pegawai') === 'keluar')>Keluar</option>
                    <option value="meninggal" @selected(old('status_pegawai') === 'meninggal')>Meninggal</option>
                </select>
                @error('status_pegawai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Status Berlaku</label>
                <input type="date" name="nonaktif_sejak" class="form-control @error('nonaktif_sejak') is-invalid @enderror" value="{{ old('nonaktif_sejak') }}">
                @error('nonaktif_sejak')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Catatan Status</label>
                <input type="text" name="catatan_status" class="form-control @error('catatan_status') is-invalid @enderror" value="{{ old('catatan_status') }}" placeholder="Contoh: Mutasi ke OPD lain per Maret 2026">
                @error('catatan_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Kelas Jabatan</label>
                <input type="hidden" name="kelas_jabatan_id" id="kelas_jabatan_id" value="{{ old('kelas_jabatan_id') }}">
                <input type="text" id="kelas_jabatan_label" class="form-control @error('kelas_jabatan_id') is-invalid @enderror" value="@php($selectedKelas = $kelas->firstWhere('id', (int) old('kelas_jabatan_id'))){{ $selectedKelas ? 'Kelas ' . $selectedKelas->nomor_kelas : '' }}" placeholder="Akan otomatis mengikuti nama jabatan" readonly>
                <div class="form-text">Kelas jabatan akan terisi otomatis mengikuti nama jabatan yang dipilih.</div>
                @error('kelas_jabatan_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

@if(auth()->user()->isSuperAdmin())
            <div class="col-md-6">
                <label class="form-label fw-semibold">Unit Kerja</label>
                <select name="unit_kerja_id" class="form-select @error('unit_kerja_id') is-invalid @enderror" required>
                    <option value="">Pilih unit kerja</option>
                    @foreach($unitKerjas as $unit)
                        <option value="{{ $unit->id }}" {{ (string) old('unit_kerja_id', $selectedUnitKerjaId) === (string) $unit->id ? 'selected' : '' }}>{{ $unit->nama_unit }}</option>
                    @endforeach
                </select>
                @error('unit_kerja_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
@else
            <div class="col-md-6">
                <label class="form-label fw-semibold">Unit Kerja</label>
                <input type="text" class="form-control" value="{{ auth()->user()->unitKerja->nama_unit ?? '-' }}" disabled>
                <div class="form-text">Data pegawai otomatis masuk ke unit kerja akun Anda.</div>
            </div>
@endif

            <div class="col-12 pt-2"><h5 class="mb-0">Bank, Kontak, dan Foto</h5></div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Nomor Rekening</label>
                <input type="text" name="nomor_rekening" class="form-control @error('nomor_rekening') is-invalid @enderror" value="{{ old('nomor_rekening') }}">
                @error('nomor_rekening')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Kode Bank</label>
                <input type="text" name="kode_bank" class="form-control @error('kode_bank') is-invalid @enderror" value="{{ old('kode_bank') }}">
                @error('kode_bank')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Nama Bank</label>
                <input type="text" name="nama_bank" class="form-control @error('nama_bank') is-invalid @enderror" value="{{ old('nama_bank') }}">
                @error('nama_bank')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Foto Profil</label>
                <input type="file" name="foto_profil" class="form-control @error('foto_profil') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp">
                <div class="form-text">Upload manual. Tidak disertakan dalam template Excel.</div>
                @error('foto_profil')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-12">
                <label class="form-label fw-semibold">Alamat</label>
                <textarea name="alamat" rows="3" class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat') }}</textarea>
                @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4 pt-2">
            <a href="{{ route('pegawai.index') }}" class="btn btn-light border">Batal</a>
            <button type="submit" class="btn btn-primary btn-icon"><i class="bi bi-save"></i> Simpan Pegawai</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const kelasInput = document.getElementById('kelas_jabatan_id');
    const kelasLabelInput = document.getElementById('kelas_jabatan_label');
    const namaJabatanSelect = document.getElementById('nama_jabatan');
    if (!kelasInput || !namaJabatanSelect) {
        return;
    }

    function syncNamaFromKelas() {
        const kelasId = kelasInput.value;
        if (!kelasId) return;
        const matching = Array.from(namaJabatanSelect.options).find(option => option.dataset.kelasId === kelasId);
        if (matching) {
            namaJabatanSelect.value = matching.value;
            if (kelasLabelInput) kelasLabelInput.value = matching.dataset.nomorLabel || '';
        }
    }

    function syncKelasFromNama() {
        const selectedOption = namaJabatanSelect.options[namaJabatanSelect.selectedIndex];
        const kelasId = selectedOption ? selectedOption.dataset.kelasId : '';
        if (kelasId) {
            kelasInput.value = kelasId;
            if (kelasLabelInput) kelasLabelInput.value = selectedOption.dataset.nomorLabel || '';
        } else {
            kelasInput.value = '';
            if (kelasLabelInput) kelasLabelInput.value = '';
        }
    }

    namaJabatanSelect.addEventListener('change', syncKelasFromNama);

    if (!namaJabatanSelect.value && kelasInput.value) {
        syncNamaFromKelas();
    } else {
        syncKelasFromNama();
    }
});
</script>
@endpush
