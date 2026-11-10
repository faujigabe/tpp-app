<?php

namespace App\Services;

use App\Models\Tpp;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Client untuk OpenWA (self-hosted WhatsApp API Gateway).
 *
 * OpenWA berjalan sebagai layanan TERPISAH dari Laravel (Node.js/Docker,
 * default http://localhost:2785). Service ini hanya memanggil REST API-nya:
 *
 *   POST {base}/sessions/{sessionId}/messages/send-text
 *   Header: X-API-Key: {apiKey}
 *   Body:   {"chatId": "628xxxx@c.us", "text": "..."}
 *
 * Konfigurasi dibaca dari config/services.php (grup 'openwa'),
 * diisi lewat .env: OPENWA_ENABLED, OPENWA_BASE_URL, OPENWA_API_KEY,
 * OPENWA_SESSION_ID.
 */
class OpenWaService
{
    public function __construct(
        private WhatsappMessageBuilder $builder,
    ) {}

    public function enabled(): bool
    {
        return (bool) config('services.openwa.enabled')
            && filled(config('services.openwa.api_key'))
            && filled(config('services.openwa.session_id'));
    }

    /** Cek sesi OpenWA: ['ok' => bool, 'status' => ?, 'error' => ?] */
    public function sessionStatus(): array
    {
        try {
            $response = $this->request()->get($this->url('/sessions/' . $this->sessionId()));

            return [
                'ok' => $response->successful(),
                'status' => $response->json('status') ?? $response->json('data.status'),
                'body' => $response->json(),
                'error' => $response->successful() ? null : 'HTTP ' . $response->status(),
            ];
        } catch (Throwable $e) {
            return ['ok' => false, 'status' => null, 'body' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Kirim satu pesan teks. Tidak pernah melempar exception ke pemanggil;
     * hasil selalu array: ['ok' => bool, 'chat_id' => ?, 'error' => ?]
     */
    public function sendText(?string $phone, string $text): array
    {
        $chatId = $this->builder->chatId($phone);

        if (!$chatId) {
            return ['ok' => false, 'chat_id' => null, 'error' => 'Nomor WhatsApp tidak valid/kosong.'];
        }

        if (!$this->enabled()) {
            return ['ok' => false, 'chat_id' => $chatId, 'error' => 'OpenWA belum aktif: periksa OPENWA_ENABLED, OPENWA_API_KEY, OPENWA_SESSION_ID di .env.'];
        }

        try {
            $response = $this->request()->post(
                $this->url('/sessions/' . $this->sessionId() . '/messages/send-text'),
                ['chatId' => $chatId, 'text' => $text],
            );

            if (!$response->successful()) {
                Log::warning('OpenWA send-text gagal', [
                    'chat_id' => $chatId,
                    'http' => $response->status(),
                    'body' => $response->body(),
                ]);

                return ['ok' => false, 'chat_id' => $chatId, 'error' => 'HTTP ' . $response->status() . ': ' . $response->body()];
            }

            return ['ok' => true, 'chat_id' => $chatId, 'error' => null, 'body' => $response->json()];
        } catch (Throwable $e) {
            Log::warning('OpenWA tidak terjangkau', ['chat_id' => $chatId, 'error' => $e->getMessage()]);

            return ['ok' => false, 'chat_id' => $chatId, 'error' => $e->getMessage()];
        }
    }

    /** Kirim rincian TPP satu pegawai (pesan sama persis dengan versi wa.me). */
    public function sendTpp(Tpp $tpp): array
    {
        return $this->sendText($tpp->nomor_whatsapp, $this->builder->buildMessage($tpp));
    }

    private function request()
    {
        return Http::acceptJson()
            ->withHeaders(['X-API-Key' => (string) config('services.openwa.api_key')])
            ->connectTimeout(5)
            ->timeout((int) config('services.openwa.timeout', 30));
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.openwa.base_url'), '/') . $path;
    }

    private function sessionId(): string
    {
        return (string) config('services.openwa.session_id');
    }
}
