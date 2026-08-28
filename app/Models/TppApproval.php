<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TppApproval extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_LOCKED = 'locked';

    protected $fillable = [
        'unit_kerja_id',
        'bulan',
        'tahun',
        'status',
        'submitted_by',
        'submitted_at',
        'locked_by',
        'locked_at',
        'unlocked_by',
        'unlocked_at',
        'catatan',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'locked_at' => 'datetime',
        'unlocked_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
            self::STATUS_LOCKED,
        ];
    }

    public static function labelFor(?string $status): string
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Menunggu Validasi',
            self::STATUS_LOCKED => 'Sudah Tervalidasi',
        ][$status ?? self::STATUS_DRAFT] ?? 'Draft';
    }

    public static function badgeClassFor(?string $status): string
    {
        return [
            self::STATUS_DRAFT => 'text-bg-secondary',
            self::STATUS_SUBMITTED => 'text-bg-warning',
            self::STATUS_LOCKED => 'text-bg-success',
        ][$status ?? self::STATUS_DRAFT] ?? 'text-bg-secondary';
    }


    public static function alertClassFor(?string $status): string
    {
        return [
            self::STATUS_DRAFT => 'alert-secondary',
            self::STATUS_SUBMITTED => 'alert-warning',
            self::STATUS_LOCKED => 'alert-success',
        ][$status ?? self::STATUS_DRAFT] ?? 'alert-secondary';
    }

    public static function dotClassFor(?string $status): string
    {
        return [
            self::STATUS_DRAFT => 'bg-secondary',
            self::STATUS_SUBMITTED => 'bg-warning',
            self::STATUS_LOCKED => 'bg-success',
        ][$status ?? self::STATUS_DRAFT] ?? 'bg-secondary';
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function unlockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }

    public function normalizedStatus(): string
    {
        if ($this->status === null || $this->status === '') {
            return self::STATUS_DRAFT;
        }

        // Status tidak dikenal harus gagal aman dan tidak membuka izin edit.
        return in_array($this->status, self::statuses(), true)
            ? $this->status
            : self::STATUS_LOCKED;
    }

    public function isDraft(): bool
    {
        return $this->normalizedStatus() === self::STATUS_DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->normalizedStatus() === self::STATUS_SUBMITTED;
    }

    public function isLocked(): bool
    {
        return $this->normalizedStatus() === self::STATUS_LOCKED;
    }

    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    public function canBeSubmitted(): bool
    {
        return $this->isDraft();
    }

    public function canBeLocked(): bool
    {
        return $this->isSubmitted();
    }

    public function canBeUnlocked(): bool
    {
        return $this->isSubmitted() || $this->isLocked();
    }

    public function appendHistory(string $actionLabel, ?string $actorName = null, ?string $note = null): void
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', (string) $this->catatan) ?: [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values();

        $entry = '[' . now('Asia/Jakarta')->format('d-m-Y H:i') . '] ' . $actionLabel;

        if ($actorName) {
            $entry .= ' oleh ' . $actorName;
        }

        if ($note = trim((string) $note)) {
            $entry .= ' | Catatan: ' . $note;
        }

        $lines->push($entry);
        $this->catatan = $lines->implode(PHP_EOL);
    }
}
