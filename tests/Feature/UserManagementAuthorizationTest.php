<?php

namespace Tests\Feature;

use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_cannot_access_user_management(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);

        $this->actingAs($operator)
            ->get('/users')
            ->assertForbidden();
    }

    public function test_admin_cannot_create_another_admin(): void
    {
        $unitKerja = UnitKerja::query()->firstOrCreate(
            ['kode_unit' => 'TEST'],
            ['nama_unit' => 'Unit Pengujian']
        );
        $admin = User::factory()->create([
            'role' => 'admin',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Admin Baru',
            'email' => 'admin-baru@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'admin-baru@example.com']);
    }
}
