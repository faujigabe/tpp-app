<?php

namespace App\Console\Commands;

use App\Models\Tpp;
use App\Services\OpenWaService;
use App\Services\WhatsappMessageBuilder;
use Illuminate\Console\Command;

class SendTppWhatsapp extends Command
{
    protected $signature = 'tpp:send-wa
        {bulan : Bulan periode TPP (1-12)}
        {tahun : Tahun periode TPP}
        {--unit= : Batasi ke unit_kerja_id tertentu}
        {--limit= : Batasi jumlah penerima}
        {--delay=8 : Jeda antar pesan (detik) — jangan diisi 0, rawan dibatasi WhatsApp}
        {--send : Benar-benar kirim. Tanpa opsi ini hanya simulasi (dry-run)}';

    protected $description = 'Kirim rincian TPP per pegawai lewat WhatsApp Gateway (OpenWA). Default: dry-run.';

    public function handle(OpenWaService $openWa, WhatsappMessageBuilder $builder): int
    {
        $bulan = (int) $this->argument('bulan');
        $tahun = (int) $this->argument('tahun');
        $send = (bool) $this->option('send');
        $delay = max(0, (int) $this->option('delay'));

        if ($bulan < 1 || $bulan > 12) {
            $this->error('Bulan harus 1-12.');

            return self::FAILURE;
        }

        $query = Tpp::query()
            ->with(['pegawai.unitKerja', 'unitKerja'])
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->when($this->option('unit'), fn ($q, $unit) => $q->forUnit((int) $unit))
            ->orderBy('id');

        if ($this->option('limit')) {
            $query->limit((int) $this->option('limit'));
        }

        $tpps = $query->get();

        if ($tpps->isEmpty()) {
            $this->warn("Tidak ada data TPP untuk periode {$bulan}/{$tahun}.");

            return self::SUCCESS;
        }

        if ($send && !$openWa->enabled()) {
            $this->error('OpenWA belum aktif. Isi OPENWA_ENABLED=true, OPENWA_API_KEY, dan OPENWA_SESSION_ID di .env, lalu jalankan: php artisan config:clear');

            return self::FAILURE;
        }

        if ($send) {
            $status = $openWa->sessionStatus();
            $this->info('Status sesi OpenWA: ' . ($status['ok'] ? 'terhubung (' . ($status['status'] ?? 'ok') . ')' : 'GAGAL — ' . $status['error']));
            if (!$status['ok']) {
                $this->error('Sesi OpenWA tidak terhubung. Cek dashboard OpenWA: sesi harus sudah scan QR dan berstatus connected.');

                return self::FAILURE;
            }
        }

        $this->info(($send ? 'MENGIRIM' : 'DRY-RUN (tidak ada pesan terkirim — tambahkan --send untuk mengirim)')
            . " rincian TPP {$bulan}/{$tahun} ke {$tpps->count()} pegawai, jeda {$delay} detik.");

        $ok = $gagal = $tanpaNomor = 0;

        foreach ($tpps as $i => $tpp) {
            $nama = $tpp->referensi_nama;
            $chatId = $builder->chatId($tpp->nomor_whatsapp);

            if (!$chatId) {
                $tanpaNomor++;
                $this->line("[lewati] {$nama} — nomor WA kosong/tidak valid");
                continue;
            }

            if (!$send) {
                $ok++;
                $this->line("[dry-run] {$nama} -> {$chatId}");
            } else {
                $result = $openWa->sendTpp($tpp);
                if ($result['ok']) {
                    $ok++;
                    $this->line("[terkirim] {$nama} -> {$chatId}");
                } else {
                    $gagal++;
                    $this->line("[GAGAL] {$nama} -> {$chatId} — {$result['error']}");
                }

                if ($delay > 0 && $i < $tpps->count() - 1) {
                    sleep($delay);
                }
            }
        }

        $this->newLine();
        $this->info("Selesai. " . ($send ? "Terkirim: {$ok}" : "Siap kirim: {$ok}") . ", gagal: {$gagal}, tanpa nomor valid: {$tanpaNomor}.");

        return $gagal > 0 ? self::FAILURE : self::SUCCESS;
    }
}
