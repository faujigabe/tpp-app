<?php

namespace App\Observers;

use App\Models\Pegawai;
use App\Services\ViewerAccountSyncService;

class PegawaiObserver
{
    public function __construct(private ViewerAccountSyncService $viewerAccountSyncService)
    {
    }

    public function saved(Pegawai $pegawai): void
    {
        $this->viewerAccountSyncService->sync($pegawai);
    }
}
