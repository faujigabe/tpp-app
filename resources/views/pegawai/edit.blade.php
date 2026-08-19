@extends('layouts.main')

@section('title', 'Edit Pegawai')

@section('content')
@php
    $selectedKelasId = (string) old('kelas_jabatan_id', $pegawai->kelas_jabatan_id);
    $selectedNamaJabatan = old('nama_jabatan', $pegawai->nama_jabatan);
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h3 class="mb-1">Edit Pegawai</h3>
        <p class="text-muted mb-0">Perbarui identitas, jabatan, kelas jabatan, dan data pendukung pegawai secara rapi dan konsisten.</p>
    </div>
    <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary btn-icon rounded-pill px-3">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded-4">
        <div class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle me-1"></i> Periksa kembali data pegawai.</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('pegawai.update', $pegawai) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-wrap align-items-center gap-3 mb-4 pb-2 border-bottom">
                @if($fotoPegawaiUrl)
                    <img src="{{ $fotoPegawaiUrl }}" alt="Foto {{ $pegawai->nama }}" style="width:72px;height:72px;object-fit:cover;border-radius:20px;border:1px solid #dee2e6;">
                @else
                    <div class="d-flex align-items-center justify-content-center bg-light" style="width:72px;height:72px;border-radius:20px;border:1px solid #dee2e6;">
                        <i class="bi bi-person fs-2 text-secondary"></i>
                    </div>
                @endif
                <div>
                    <div class="text-muted small mb-1">Data yang sedang diedit</div>
                    <h4 class="mb-1">{{ $pegawai->nama }}</h4>
                    <div class="text-muted">NIP: {{ $pegawai->nip ?: '-' }} · Unit: {{ $pegawai->unitKerja->nama_unit ?? (auth()->user()->unitKerja->nama_unit ?? '-') }}</div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <div class="bg-light rounded-4 p-3 border">
                        <div class="fw-semibold mb-1"><i class="bi bi-person-vcard me-2 text-primary"></i>Identitas Pegawai</div>
                        <div class="text-muted small">Lengkapi informasi dasar pegawai untuk kebutuhan administrasi dan perhitungan TPP.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Pegawai</label>
                    <input type="text" name="nama" class="form-control form-control-lg rounded-3 @error('nama') is-invalid @enderror" value="{{ old('nama', $pegawai->nama) }}" required>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">NIP</label>
                    <input type="text" name="nip" class="form-control rounded-3 @error('nip') is-invalid @enderror" value="{{ old('nip', $pegawai->nip) }}" required>
                    @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">NIK</label>
                    <input type="text" name="nik" class="form-control rounded-3 @error('nik') is-invalid @enderror" value="{{ old('nik', $pegawai->nik) }}">
                    @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">No. NPWP</label>
                    <input type="text" name="no_npwp" class="form-control rounded-3 @error('no_npwp') is-invalid @enderror" value="{{ old('no_npwp', $pegawai->no_npwp) }}">
                    @error('no_npwp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control rounded-3 @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir', $pegawai->tanggal_lahir) }}">
                    @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Golongan</label>
                    <select name="golongan" class="form-select rounded-3 @error('golongan') is-invalid @enderror" required>
                        <option value="">Pilih golongan</option>
                        @foreach(['II/A','II/B','II/C','II/D','III/A','III/B','III/C','III/D','IV/A','IV/B','IV/C','IV/D','IV/E'] as $gol)
                            <option value="{{ $gol }}" @selected(old('golongan', $pegawai->golongan) === $gol)>{{ $gol }}</option>
                        @endforeach
                    </select>
                    @error('golongan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Agama</label>
                    <input type="text" name="agama" class="form-control rounded-3 @error('agama') is-invalid @enderror" value="{{ old('agama', $pegawai->agama) }}" required>
                    @error('agama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">No. HP</label>
                    <input type="text" name="no_hp" class="form-control rounded-3 @error('no_hp') is-invalid @enderror" value="{{ old('no_hp', $pegawai->no_hp) }}" required>
                    @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 mt-2">
                    <div class="bg-light rounded-4 p-3 border">
                        <div class="fw-semibold mb-1"><i class="bi bi-briefcase me-2 text-primary"></i>Jabatan dan Kelas Jabatan</div>
                        <div class="text-muted small">Pilih nama jabatan terlebih dahulu. Kelas jabatan akan mengikuti pilihan nama jabatan yang dipilih.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Jabatan</label>
                    <textarea name="jabatan" rows="2" class="form-control rounded-3 @error('jabatan') is-invalid @enderror" required>{{ old('jabatan', $pegawai->jabatan) }}</textarea>
                    @error('jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Jabatan</label>
                    <select name="nama_jabatan" id="nama_jabatan" class="form-select rounded-3 @error('nama_jabatan') is-invalid @enderror">
                        <option value="">Pilih nama jabatan</option>
                        @foreach($namaJabatanOptions as $option)
                            <option value="{{ $option['value'] }}"
                                    data-kelas-id="{{ $option['kelas_jabatan_id'] }}"
                                    data-nomor-label="{{ $option['nomor_kelas_label'] }}" data-display-label="{{ $option['label'] }}"
                                    @selected($selectedNamaJabatan === $option['value'] && $selectedKelasId === (string) $option['kelas_jabatan_id'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Yang tersimpan hanya teks nama jabatan.</div>
                    @error('nama_jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kelas Jabatan</label>
                    <input type="hidden" name="kelas_jabatan_id" id="kelas_jabatan_id" value="{{ $selectedKelasId }}">
                    <input type="text" id="kelas_jabatan_label" class="form-control rounded-3 @error('kelas_jabatan_id') is-invalid @enderror" value="@php($selectedKelas = $kelas->firstWhere('id', (int) $selectedKelasId)){{ $selectedKelas ? 'Kelas ' . $selectedKelas->nomor_kelas : '' }}" placeholder="Akan otomatis mengikuti nama jabatan" readonly>
                    <div class="form-text">Kelas jabatan akan terisi otomatis mengikuti nama jabatan yang dipilih.</div>
                    @error('kelas_jabatan_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tipe Jabatan</label>
                    <input type="number" name="tipe_jabatan" class="form-control rounded-3 @error('tipe_jabatan') is-invalid @enderror" value="{{ old('tipe_jabatan', $pegawai->tipe_jabatan) }}" min="0">
                    @error('tipe_jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Eselon</label>
                    <input type="text" name="eselon" class="form-control rounded-3 @error('eselon') is-invalid @enderror" value="{{ old('eselon', $pegawai->eselon) }}">
                    @error('eselon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status ASN</label>
                    <input type="number" name="status_asn" class="form-control rounded-3 @error('status_asn') is-invalid @enderror" value="{{ old('status_asn', $pegawai->status_asn) }}" min="0">
                    @error('status_asn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Masa Kerja Golongan</label>
                    <input type="number" name="masa_kerja_golongan" class="form-control rounded-3 @error('masa_kerja_golongan') is-invalid @enderror" value="{{ old('masa_kerja_golongan', $pegawai->masa_kerja_golongan) }}" min="0">
                    @error('masa_kerja_golongan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status Pegawai</label>
                    <select name="status_pegawai" class="form-select rounded-3 @error('status_pegawai') is-invalid @enderror">
                        <option value="aktif" @selected(old('status_pegawai', $pegawai->status_pegawai ?? 'aktif') === 'aktif')>Aktif</option>
                        <option value="mutasi" @selected(old('status_pegawai', $pegawai->status_pegawai ?? 'aktif') === 'mutasi')>Mutasi</option>
                        <option value="pensiun" @selected(old('status_pegawai', $pegawai->status_pegawai ?? 'aktif') === 'pensiun')>Pensiun</option>
                        <option value="keluar" @selected(old('status_pegawai', $pegawai->status_pegawai ?? 'aktif') === 'keluar')>Keluar</option>
                        <option value="meninggal" @selected(old('status_pegawai', $pegawai->status_pegawai ?? 'aktif') === 'meninggal')>Meninggal</option>
                    </select>
                    @error('status_pegawai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal Status Berlaku</label>
                    <input type="date" name="nonaktif_sejak" class="form-control rounded-3 @error('nonaktif_sejak') is-invalid @enderror" value="{{ old('nonaktif_sejak', optional($pegawai->nonaktif_sejak)->format('Y-m-d')) }}">
                    @error('nonaktif_sejak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Catatan Status</label>
                    <input type="text" name="catatan_status" class="form-control rounded-3 @error('catatan_status') is-invalid @enderror" value="{{ old('catatan_status', $pegawai->catatan_status) }}" placeholder="Contoh: Mutasi ke OPD lain per Maret 2026">
                    @error('catatan_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                @if(auth()->user()->isSuperAdmin())
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Unit Kerja</label>
                        <select name="unit_kerja_id" class="form-select rounded-3 @error('unit_kerja_id') is-invalid @enderror" required>
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
                        <input type="text" class="form-control rounded-3" value="{{ auth()->user()->unitKerja->nama_unit ?? '-' }}" disabled>
                        <div class="form-text">Data pegawai tetap berada pada unit kerja akun Anda.</div>
                    </div>
                @endif

                <div class="col-12 mt-2">
                    <div class="bg-light rounded-4 p-3 border">
                        <div class="fw-semibold mb-1"><i class="bi bi-bank me-2 text-primary"></i>Bank, Kontak, dan Foto</div>
                        <div class="text-muted small">Lengkapi data rekening, alamat, dan foto profil bila diperlukan.</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Nomor Rekening</label>
                    <input type="text" name="nomor_rekening" class="form-control rounded-3 @error('nomor_rekening') is-invalid @enderror" value="{{ old('nomor_rekening', $pegawai->nomor_rekening) }}">
                    @error('nomor_rekening')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Kode Bank</label>
                    <input type="text" name="kode_bank" class="form-control rounded-3 @error('kode_bank') is-invalid @enderror" value="{{ old('kode_bank', $pegawai->kode_bank) }}">
                    @error('kode_bank')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Nama Bank</label>
                    <input type="text" name="nama_bank" class="form-control rounded-3 @error('nama_bank') is-invalid @enderror" value="{{ old('nama_bank', $pegawai->nama_bank) }}">
                    @error('nama_bank')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Foto Profil</label>
                    <input type="file" name="foto_profil" class="form-control rounded-3 @error('foto_profil') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp">
                    <input type="hidden" name="hapus_foto_profil" value="0" id="hapus-foto-admin-input">
                    <div class="form-text">Unggah manual bila ingin memperbarui foto profil.</div>
                    @error('foto_profil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($fotoPegawaiUrl)
                        <button type="button" class="btn btn-outline-danger btn-sm mt-3 rounded-pill" id="hapus-foto-admin-btn"><i class="bi bi-trash3 me-1"></i>Hapus Foto Saat Disimpan</button>
                    @endif
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Alamat</label>
                    <textarea name="alamat" rows="3" class="form-control rounded-3 @error('alamat') is-invalid @enderror">{{ old('alamat', $pegawai->alamat) }}</textarea>
                    @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-0 px-4 px-lg-5 pb-4 pt-0">
            <div class="d-flex justify-content-end gap-2 flex-wrap">
                <a href="{{ route('pegawai.index') }}" class="btn btn-light border rounded-pill px-4">Batal</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="bi bi-save me-1"></i> Update Pegawai</button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const removeBtn = document.getElementById('hapus-foto-admin-btn');
    const removeInput = document.getElementById('hapus-foto-admin-input');
    const kelasInput = document.getElementById('kelas_jabatan_id');
    const kelasLabelInput = document.getElementById('kelas_jabatan_label');
    const namaJabatanSelect = document.getElementById('nama_jabatan');

    function syncNamaFromKelas() {
        if (!kelasInput || !namaJabatanSelect) return;
        const kelasId = kelasInput.value;
        if (!kelasId) return;
        const matching = Array.from(namaJabatanSelect.options).find(option => option.dataset.kelasId === kelasId);
        if (matching) {
            namaJabatanSelect.value = matching.value;
            if (kelasLabelInput) kelasLabelInput.value = matching.dataset.nomorLabel || '';
        }
    }

    function syncKelasFromNama() {
        if (!kelasInput || !namaJabatanSelect) return;
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

    if (namaJabatanSelect) {
        namaJabatanSelect.addEventListener('change', syncKelasFromNama);
    }

    if (!namaJabatanSelect.value && kelasInput && kelasInput.value) {
        syncNamaFromKelas();
    } else {
        syncKelasFromNama();
    }

    if (!removeBtn || !removeInput) {
        return;
    }

    removeBtn.addEventListener('click', function () {
        if (!window.confirm('Hapus foto profil pegawai ini?')) {
            return;
        }

        removeInput.value = '1';
        this.classList.add('disabled');
        this.setAttribute('aria-disabled', 'true');
        this.innerHTML = '<i class="bi bi-check2 me-1"></i>Foto akan dihapus';
    });
});
</script>
@endpush
@endsection
