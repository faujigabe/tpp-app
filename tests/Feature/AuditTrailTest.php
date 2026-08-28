<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\KelasJabatan;
use App\Models\Pegawai;
use App\Models\Tpp;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    public function test_perubahan_tpp_mencatat_pelaku_dan_nilai_sebelum_sesudah(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass('TPP');
        $actor = User::factory()->create(['role' => 'operator', 'unit_kerja_id' => $unit->id]);
        $pegawai = $this->makePegawai($unit, $kelas);
        $tpp = $this->makeTpp($pegawai, $unit);

        $this->actingAs($actor);
        $tpp->update(['produktivitas' => 80]);

        $log = AuditLog::query()
            ->where('auditable_type', Tpp::class)
            ->where('auditable_id', $tpp->id)
            ->where('event', 'updated')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($actor->id, $log->user_id);
        $this->assertSame($actor->name, $log->actor_name);
        $this->assertEquals(100, $log->old_values['produktivitas']);
        $this->assertEquals(80, $log->new_values['produktivitas']);
    }

    public function test_penghapusan_menyimpan_snapshot_data_yang_dihapus(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass('HAPUS');
        $actor = User::factory()->create(['role' => 'operator', 'unit_kerja_id' => $unit->id]);
        $pegawai = $this->makePegawai($unit, $kelas);
        $pegawaiId = $pegawai->id;

        $this->actingAs($actor);
        $pegawai->delete();

        $log = AuditLog::query()
            ->where('auditable_type', Pegawai::class)
            ->where('auditable_id', $pegawaiId)
            ->where('event', 'deleted')
            ->firstOrFail();

        $this->assertSame($pegawai->nip, $log->old_values['nip']);
        $this->assertNull($log->new_values);
    }

    public function test_password_dan_token_tidak_pernah_disimpan_di_audit_log(): void
    {
        [$unit] = $this->makeUnitAndClass('RAHASIA');
        $actor = User::factory()->create(['role' => 'super_admin']);
        $target = User::factory()->create(['role' => 'operator', 'unit_kerja_id' => $unit->id]);

        $this->actingAs($actor);
        $target->update(['password' => Hash::make('password-baru-yang-aman')]);

        $this->assertDatabaseMissing('audit_logs', [
            'auditable_type' => User::class,
            'auditable_id' => $target->id,
            'event' => 'updated',
        ]);
        $this->assertStringNotContainsString('password-baru-yang-aman', AuditLog::query()->get()->toJson());
    }

    public function test_hanya_super_admin_dapat_membuka_jejak_perubahan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)->get(route('audit-logs.index'))->assertForbidden();
        $this->actingAs($superAdmin)->get(route('audit-logs.index'))->assertOk();
    }

    public function test_audit_log_lebih_lama_dari_lima_tahun_dibersihkan(): void
    {
        config(['backup.audit_retention_years' => 5]);
        AuditLog::query()->create([
            'event' => 'updated',
            'auditable_type' => Tpp::class,
            'auditable_id' => 99,
            'created_at' => now()->subYears(6),
        ]);
        $recent = AuditLog::query()->create([
            'event' => 'updated',
            'auditable_type' => Tpp::class,
            'auditable_id' => 100,
            'created_at' => now()->subYears(4),
        ]);

        $this->artisan('audit:prune')->assertSuccessful();

        $this->assertDatabaseMissing('audit_logs', ['auditable_id' => 99]);
        $this->assertDatabaseHas('audit_logs', ['id' => $recent->id]);
    }

    private function makeUnitAndClass(string $suffix): array
    {
        $unit = UnitKerja::query()->create(['nama_unit' => 'Unit ' . $suffix, 'kode_unit' => $suffix]);
        $kelas = KelasJabatan::query()->create([
            'unit_kerja_id' => $unit->id,
            'nomor_kelas' => 7,
            'nama_kelas' => 'Kelas ' . $suffix,
            'beban_kerja' => 1000000,
            'prestasi_kerja' => 500000,
            'kondisi_kerja' => 0,
            'kelangkaan_profesi' => 0,
        ]);

        return [$unit, $kelas];
    }

    private function makePegawai(UnitKerja $unit, KelasJabatan $kelas): Pegawai
    {
        return Pegawai::query()->create([
            'nama' => 'Pegawai ' . $unit->kode_unit,
            'nip' => 'NIP-' . $unit->kode_unit,
            'no_hp' => '081234567890',
            'golongan' => 'III/A',
            'jabatan' => 'Pelaksana',
            'agama' => 'Islam',
            'kelas_jabatan_id' => $kelas->id,
            'unit_kerja_id' => $unit->id,
            'status_pegawai' => Pegawai::STATUS_AKTIF,
        ]);
    }

    private function makeTpp(Pegawai $pegawai, UnitKerja $unit): Tpp
    {
        return Tpp::query()->create([
            'pegawai_id' => $pegawai->id,
            'unit_kerja_id' => $unit->id,
            'bulan' => 1,
            'tahun' => 2026,
            'produktivitas' => 100,
            'kehadiran' => 100,
            'perilaku' => 100,
            'iuran_wajib' => 0,
            'tpp_kotor' => 1500000,
            'pajak' => 0,
            'zakat' => 0,
            'total_diterima' => 1500000,
        ]);
    }
}
