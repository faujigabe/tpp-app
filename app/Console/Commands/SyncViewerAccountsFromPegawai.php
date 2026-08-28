<?php

namespace App\Console\Commands;

use App\Models\Pegawai;
use App\Models\User;
use App\Services\ViewerAccountSyncService;
use Illuminate\Console\Command;

class SyncViewerAccountsFromPegawai extends Command
{
    protected $signature = 'pegawai:sync-viewers {--reset-password : Reset password viewer yang sudah ada sesuai tanggal lahir}';

    protected $description = 'Membuat akun viewer pegawai aktif dengan kredensial awal NIP dan password sementara tanggal lahir format ddmmyyyy';

    public function __construct(private ViewerAccountSyncService $viewerAccountSyncService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $pegawais = Pegawai::query()->orderBy('nama')->get();

        if ($pegawais->isEmpty()) {
            $this->warn('Tidak ada data pegawai untuk diproses.');
            return self::SUCCESS;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $resetPassword = (bool) $this->option('reset-password');

        foreach ($pegawais as $pegawai) {
            if (! $pegawai->isAktif()) {
                $this->warn("Lewati {$pegawai->nama} (ID {$pegawai->id}) karena status pegawai tidak aktif.");
                $skipped++;
                continue;
            }

            $nip = trim((string) ($pegawai->nip ?? ''));
            $tanggalLahir = $pegawai->tanggal_lahir;

            if ($nip === '' || empty($tanggalLahir)) {
                $this->warn("Lewati {$pegawai->nama} (ID {$pegawai->id}) karena NIP atau tanggal lahir kosong.");
                $skipped++;
                continue;
            }

            $timestamp = strtotime((string) $tanggalLahir);
            if ($timestamp === false) {
                $this->warn("Lewati {$pegawai->nama} (ID {$pegawai->id}) karena format tanggal lahir tidak valid: {$tanggalLahir}");
                $skipped++;
                continue;
            }

            $viewerExists = User::query()->where('pegawai_id', $pegawai->id)->exists();

            try {
                $this->viewerAccountSyncService->sync($pegawai, $resetPassword);

                if ($viewerExists) {
                    $updated++;
                    $label = $resetPassword ? '[UPDATE + RESET PASSWORD]' : '[UPDATE]';
                    $this->line("{$label} {$pegawai->nama} | NIP: {$nip}");
                } else {
                    $created++;
                    $this->info("[BUAT] {$pegawai->nama} | NIP: {$nip}");
                }
            } catch (\Throwable $e) {
                $errors++;
                $this->error("[GAGAL] {$pegawai->nama} (ID {$pegawai->id}) : {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Sinkronisasi selesai. Dibuat: {$created}, Diupdate: {$updated}, Dilewati: {$skipped}, Gagal: {$errors}");
        $this->line('Password sementara tidak ditampilkan pada log. Sampaikan kredensial awal melalui saluran yang aman.');

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
