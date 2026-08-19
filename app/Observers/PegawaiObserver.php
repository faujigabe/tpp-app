<?php

namespace App\Observers;

use App\Models\Pegawai;
use App\Models\Tpp;
use App\Services\ViewerAccountSyncService;

class PegawaiObserver
{
    public function __construct(private ViewerAccountSyncService $viewerAccountSyncService)
    {
    }

    public function saved(Pegawai $pegawai): void
    {
        $this->viewerAccountSyncService->sync($pegawai);

        if ($pegawai->unit_kerja_id) {
            Tpp::query()
                ->where('pegawai_id', $pegawai->id)
                ->where('unit_kerja_id', '!=', $pegawai->unit_kerja_id)
                ->update(['unit_kerja_id' => $pegawai->unit_kerja_id]);
        }
    }
}
