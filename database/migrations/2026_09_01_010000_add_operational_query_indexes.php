<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tpps', function (Blueprint $table) {
            $table->index(['unit_kerja_id', 'tahun', 'bulan'], 'tpps_unit_period_idx');
            $table->index(['tahun', 'bulan'], 'tpps_period_idx');
        });
        Schema::table('pegawais', function (Blueprint $table) {
            $table->index(['unit_kerja_id', 'status_pegawai', 'nonaktif_sejak'], 'pegawais_unit_status_idx');
        });
        Schema::table('tpp_approvals', function (Blueprint $table) {
            $table->index(['tahun', 'bulan', 'status'], 'tpp_approvals_period_status_idx');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->index(['unit_kerja_id', 'role'], 'users_unit_role_idx');
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_type', 'notifiable_id', 'read_at', 'created_at'], 'notifications_inbox_idx');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', fn (Blueprint $table) => $table->dropIndex('notifications_inbox_idx'));
        Schema::table('users', fn (Blueprint $table) => $table->dropIndex('users_unit_role_idx'));
        Schema::table('tpp_approvals', fn (Blueprint $table) => $table->dropIndex('tpp_approvals_period_status_idx'));
        Schema::table('pegawais', fn (Blueprint $table) => $table->dropIndex('pegawais_unit_status_idx'));
        Schema::table('tpps', function (Blueprint $table) {
            $table->dropIndex('tpps_unit_period_idx');
            $table->dropIndex('tpps_period_idx');
        });
    }
};
