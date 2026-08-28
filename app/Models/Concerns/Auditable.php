<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(fn ($model) => $model->writeAuditLog('created'));
        static::updated(fn ($model) => $model->writeAuditLog('updated'));
        static::deleted(fn ($model) => $model->writeAuditLog('deleted'));
    }

    protected function writeAuditLog(string $event): void
    {
        $fields = $this->auditFields();
        $oldValues = [];
        $newValues = [];

        if ($event === 'created') {
            $newValues = Arr::only($this->getAttributes(), $fields);
        } elseif ($event === 'deleted') {
            $oldValues = Arr::only($this->getOriginal(), $fields);
        } else {
            $changedFields = array_values(array_intersect(array_keys($this->getChanges()), $fields));
            if ($changedFields === []) {
                return;
            }

            $oldValues = Arr::only($this->getRawOriginal(), $changedFields);
            $newValues = Arr::only($this->getAttributes(), $changedFields);
        }

        $actor = Auth::user();
        $request = app()->bound('request') ? request() : null;

        AuditLog::query()->create([
            'user_id' => $actor?->id,
            'unit_kerja_id' => $actor?->unit_kerja_id ?? $this->getAttribute('unit_kerja_id'),
            'actor_name' => $actor?->name,
            'actor_role' => $actor?->role,
            'event' => $event,
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => app()->runningInConsole() ? null : $request?->ip(),
            'user_agent' => app()->runningInConsole() ? null : mb_substr((string) $request?->userAgent(), 0, 500),
            'created_at' => now(),
        ]);
    }

    protected function auditFields(): array
    {
        return property_exists($this, 'auditInclude')
            ? $this->auditInclude
            : array_values(array_diff($this->getFillable(), [
                'password',
                'remember_token',
                'foto_profil',
            ]));
    }
}
