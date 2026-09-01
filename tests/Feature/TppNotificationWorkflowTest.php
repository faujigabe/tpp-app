<?php

namespace Tests\Feature;

use App\Models\KelasJabatan;
use App\Models\Pegawai;
use App\Models\Tpp;
use App\Models\TppApproval;
use App\Models\UnitKerja;
use App\Models\User;
use App\Notifications\TppPeriodStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TppNotificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengajuan_periode_memberi_notifikasi_kepada_super_admin_saja(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass('AJU');
        $operator = $this->makeUser($unit, 'operator');
        $adminUnit = $this->makeUser($unit, 'admin');
        $superSatu = User::factory()->create(['role' => 'super_admin']);
        $superDua = User::factory()->create(['role' => 'super_admin']);
        $pegawai = $this->makePegawai($unit, $kelas, 'AJU');
        $this->makeTpp($pegawai, $unit);

        $this->actingAs($operator)
            ->post(route('tpp.submit-period'), ['bulan' => 8, 'tahun' => 2026])
            ->assertSessionHas('success');

        $this->assertCount(1, $superSatu->fresh()->notifications);
        $this->assertCount(1, $superDua->fresh()->notifications);
        $this->assertCount(0, $adminUnit->fresh()->notifications);
        $this->assertSame('submitted', $superSatu->fresh()->notifications->first()->data['status']);
    }

    public function test_validasi_hanya_memberi_notifikasi_kepada_pengelola_unit_terkait(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass('VALID');
        [$unitLain] = $this->makeUnitAndClass('LAIN');
        $admin = $this->makeUser($unit, 'admin');
        $operator = $this->makeUser($unit, 'operator');
        $operatorLain = $this->makeUser($unitLain, 'operator');
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $pegawai = $this->makePegawai($unit, $kelas, 'VALID');
        $this->makeTpp($pegawai, $unit);
        TppApproval::query()->create([
            'unit_kerja_id' => $unit->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => TppApproval::STATUS_SUBMITTED,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('tpp.lock-period'), [
                'unit_kerja_id' => $unit->id,
                'bulan' => 8,
                'tahun' => 2026,
            ])
            ->assertSessionHas('success');

        $this->assertCount(1, $admin->fresh()->notifications);
        $this->assertCount(1, $operator->fresh()->notifications);
        $this->assertCount(0, $operatorLain->fresh()->notifications);
        $this->assertSame('locked', $operator->fresh()->notifications->first()->data['status']);
    }

    public function test_pembukaan_periode_mengirim_alasan_kepada_pengelola_unit(): void
    {
        [$unit] = $this->makeUnitAndClass('BUKA');
        $admin = $this->makeUser($unit, 'admin');
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        TppApproval::query()->create([
            'unit_kerja_id' => $unit->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => TppApproval::STATUS_LOCKED,
        ]);
        $alasan = 'Perbaikan kehadiran berdasarkan verifikasi ulang.';

        $this->actingAs($superAdmin)
            ->post(route('tpp.unlock-period'), [
                'unit_kerja_id' => $unit->id,
                'bulan' => 8,
                'tahun' => 2026,
                'alasan' => $alasan,
            ])
            ->assertSessionHas('success');

        $notification = $admin->fresh()->notifications->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString($alasan, $notification->data['message']);
        $this->assertSame('draft', $notification->data['status']);
    }

    public function test_pengguna_tidak_dapat_membaca_notifikasi_milik_akun_lain(): void
    {
        [$unit] = $this->makeUnitAndClass('MILIK');
        $pemilik = $this->makeUser($unit, 'admin');
        $penggunaLain = $this->makeUser($unit, 'operator');
        $pemilik->notify($this->notification($unit));
        $notification = $pemilik->notifications()->firstOrFail();

        $this->actingAs($penggunaLain)
            ->post(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_membuka_notifikasi_menandainya_sudah_dibaca_dan_menuju_periode(): void
    {
        [$unit] = $this->makeUnitAndClass('BACA');
        $admin = $this->makeUser($unit, 'admin');
        $admin->notify($this->notification($unit));
        $notification = $admin->notifications()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect('/tpp?unit_kerja_id=' . $unit->id . '&bulan=8&tahun=2026');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_pembersihan_hanya_menghapus_notifikasi_lama_yang_sudah_dibaca(): void
    {
        [$unit] = $this->makeUnitAndClass('PRUNE');
        $admin = $this->makeUser($unit, 'admin');
        $admin->notify($this->notification($unit));
        $lamaTerbaca = $admin->notifications()->firstOrFail();
        $lamaTerbaca->forceFill(['read_at' => now()->subDays(400), 'created_at' => now()->subDays(400)])->save();

        $admin->notify($this->notification($unit));
        $lamaBelumDibaca = $admin->notifications()->latest()->firstOrFail();
        $lamaBelumDibaca->forceFill(['created_at' => now()->subDays(400)])->save();

        $this->artisan('notifications:prune')->assertSuccessful();

        $this->assertDatabaseMissing('notifications', ['id' => $lamaTerbaca->id]);
        $this->assertDatabaseHas('notifications', ['id' => $lamaBelumDibaca->id, 'read_at' => null]);
    }

    private function notification(UnitKerja $unit): TppPeriodStatusNotification
    {
        return new TppPeriodStatusNotification(
            'Pengajuan TPP',
            'Terdapat pembaruan TPP.',
            $unit->id,
            $unit->nama_unit,
            8,
            2026,
            TppApproval::STATUS_SUBMITTED,
            'Penguji',
        );
    }

    private function makeUnitAndClass(string $suffix): array
    {
        $unit = UnitKerja::query()->create(['kode_unit' => $suffix, 'nama_unit' => 'Unit ' . $suffix]);
        $kelas = KelasJabatan::query()->create([
            'unit_kerja_id' => $unit->id,
            'nomor_kelas' => 7,
            'nama_kelas' => 'Kelas ' . $suffix,
            'beban_kerja' => 1000000,
            'prestasi_kerja' => 0,
            'kondisi_kerja' => 0,
            'kelangkaan_profesi' => 0,
        ]);

        return [$unit, $kelas];
    }

    private function makeUser(UnitKerja $unit, string $role): User
    {
        return User::factory()->create(['role' => $role, 'unit_kerja_id' => $unit->id]);
    }

    private function makePegawai(UnitKerja $unit, KelasJabatan $kelas, string $suffix): Pegawai
    {
        return Pegawai::query()->create([
            'nama' => 'Pegawai ' . $suffix,
            'nip' => 'NIP-NOTIF-' . $suffix,
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
            'bulan' => 8,
            'tahun' => 2026,
            'produktivitas' => 100,
            'kehadiran' => 100,
            'perilaku' => 100,
            'iuran_wajib' => 0,
            'tpp_kotor' => 1000000,
            'pajak' => 0,
            'zakat' => 0,
            'total_diterima' => 1000000,
        ]);
    }
}
