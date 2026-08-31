<?php

namespace App\Http\Controllers;

use App\Models\BackupRun;
use App\Support\BackupHealth;
use Illuminate\Http\Request;

class BackupMonitorController extends Controller
{
    public function index(Request $request, BackupHealth $backupHealth)
    {
        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();

        $runs = BackupRun::query()
            ->when(in_array($status, ['running', 'success', 'failed'], true), fn ($query) => $query->where('status', $status))
            ->when(in_array($type, ['daily', 'weekly'], true), fn ($query) => $query->where('type', $type))
            ->latest('started_at')
            ->paginate(25)
            ->withQueryString();

        return view('backup-monitor.index', [
            'health' => $backupHealth->summary(),
            'runs' => $runs,
            'statusFilter' => $status,
            'typeFilter' => $type,
        ]);
    }
}
