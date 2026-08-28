<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'event' => 'nullable|in:created,updated,deleted',
            'model' => 'nullable|string|max:100',
            'unit_kerja_id' => 'nullable|integer|exists:unit_kerjas,id',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'q' => 'nullable|string|max:100',
        ]);

        $logs = AuditLog::query()
            ->with(['user', 'unitKerja'])
            ->when($filters['event'] ?? null, fn ($query, $event) => $query->where('event', $event))
            ->when($filters['model'] ?? null, fn ($query, $model) => $query->where('auditable_type', $model))
            ->when($filters['unit_kerja_id'] ?? null, fn ($query, $unitId) => $query->where('unit_kerja_id', $unitId))
            ->when($filters['tanggal_mulai'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['tanggal_selesai'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['q'] ?? null, function ($query, $keyword) {
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('actor_name', 'like', '%' . $keyword . '%')
                        ->orWhere('auditable_id', $keyword)
                        ->orWhere('ip_address', 'like', '%' . $keyword . '%');
                });
            })
            ->latest('created_at')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        $models = AuditLog::query()->distinct()->orderBy('auditable_type')->pluck('auditable_type');
        $unitKerjas = UnitKerja::query()->orderBy('nama_unit')->get();

        return view('audit-logs.index', compact('logs', 'models', 'unitKerjas', 'filters'));
    }
}
