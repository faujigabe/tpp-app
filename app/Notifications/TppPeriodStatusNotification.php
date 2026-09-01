<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TppPeriodStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $title,
        private string $message,
        private int $unitKerjaId,
        private string $unitKerjaName,
        private int $bulan,
        private int $tahun,
        private string $status,
        private ?string $actorName = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'unit_kerja_id' => $this->unitKerjaId,
            'unit_kerja_name' => $this->unitKerjaName,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'status' => $this->status,
            'actor_name' => $this->actorName,
            'action_url' => route('tpp.index', [
                'unit_kerja_id' => $this->unitKerjaId,
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
            ], false),
        ];
    }
}
