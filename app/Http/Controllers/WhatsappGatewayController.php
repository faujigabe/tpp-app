<?php

namespace App\Http\Controllers;

use App\Models\Tpp;
use App\Services\OpenWaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsappGatewayController extends Controller
{
    public function __construct(
        private OpenWaService $openWa,
    ) {}

    /**
     * Status gateway untuk pemeriksaan awal dari halaman TPP sebelum
     * pengiriman massal dimulai (dipanggil lewat fetch oleh view).
     */
    public function status(Request $request): JsonResponse
    {
        abort_if($request->user()?->isSuperAdmin(), 403, 'Fitur WhatsApp hanya tersedia untuk admin/operator unit kerja.');

        if (!$this->openWa->enabled()) {
            return response()->json([
                'enabled' => false,
                'ok' => false,
                'status' => null,
                'message' => 'OpenWA belum aktif. Periksa OPENWA_ENABLED, OPENWA_API_KEY, dan OPENWA_SESSION_ID di .env.',
            ]);
        }

        $status = $this->openWa->sessionStatus();

        return response()->json([
            'enabled' => true,
            'ok' => $status['ok'],
            'status' => $status['status'],
            'message' => $status['ok']
                ? 'Sesi OpenWA terhubung.'
                : 'Sesi OpenWA tidak terhubung: ' . ($status['error'] ?? 'periksa dashboard OpenWA.'),
        ]);
    }

    /**
     * Kirim rincian TPP satu pegawai lewat gateway.
     * Halaman TPP memanggil endpoint ini satu per satu dengan jeda antar
     * panggilan, sehingga tidak ada request web yang berjalan lama.
     */
    public function send(Request $request, Tpp $tpp): JsonResponse
    {
        abort_if($request->user()?->isSuperAdmin(), 403, 'Fitur WhatsApp hanya tersedia untuk admin/operator unit kerja.');

        $resolvedUnitKerjaId = $tpp->unit_kerja_id ?? $tpp->pegawai?->unit_kerja_id;
        abort_unless($request->user()->canAccessUnit($resolvedUnitKerjaId), 403, 'Akses ditolak');

        $tpp->loadMissing(['pegawai.unitKerja', 'pegawai.kelasJabatan', 'unitKerja']);

        $result = $this->openWa->sendTpp($tpp);

        return response()->json([
            'ok' => $result['ok'],
            'id' => $tpp->id,
            'nama' => $tpp->referensi_nama,
            'chat_id' => $result['chat_id'],
            'error' => $result['error'],
        ], $result['ok'] ? 200 : 422);
    }
}
