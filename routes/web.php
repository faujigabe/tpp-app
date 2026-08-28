<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TppController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\KelasJabatanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UnitKerjaController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\AuditLogController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/photo', [ProfileController::class, 'photo'])->name('profile.photo');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/tpp', [TppController::class, 'index'])->name('tpp.index');

    Route::middleware(['role:super_admin,admin,operator'])->group(function () {
        Route::get('/tpp/cetak', [TppController::class, 'cetak'])->name('tpp.cetak');
        Route::get('/tpp/export', [TppController::class, 'exportExcel'])->name('tpp.export');
        Route::get('/tpp/rekap/export', [TppController::class, 'exportRekapExcel'])->name('tpp.rekap.export');
        Route::get('/tpp/rekap-sipd/export', [TppController::class, 'exportRekapSipdExcel'])->name('tpp.rekap.sipd.export');
    });

    Route::middleware(['role:super_admin,admin'])->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });

    Route::middleware(['role:admin,operator'])->group(function () {
        Route::get('/tpp/export-whatsapp', [TppController::class, 'exportWhatsappExcel'])->name('tpp.export.whatsapp');
        Route::get('/tpp/create', [TppController::class, 'create'])->name('tpp.create');
        Route::post('/tpp/import-ekinerja-pdf', [TppController::class, 'importEkinerjaPdf'])->name('tpp.import-ekinerja-pdf');
        Route::post('/tpp/store', [TppController::class, 'store'])->name('tpp.store');
        Route::post('/tpp/submit-period', [TppController::class, 'submitPeriod'])->name('tpp.submit-period');
        Route::get('/tpp/edit-massal', [TppController::class, 'editMassal'])->name('tpp.edit.massal');
        Route::post('/tpp/update-massal', [TppController::class, 'updateMassal'])->name('tpp.update.massal');
        Route::delete('/tpp/destroy-massal', [TppController::class, 'destroyMassal'])->name('tpp.destroy.massal');
        Route::delete('/tpp/{tpp}', [TppController::class, 'destroy'])->name('tpp.destroy');
        Route::get('/tpp/{tpp}/edit', [TppController::class, 'edit'])->name('tpp.edit');
        Route::put('/tpp/{tpp}', [TppController::class, 'update'])->name('tpp.update');
    });

    Route::middleware(['role:super_admin,admin,operator'])->group(function () {
        Route::get('pegawai/import', [PegawaiController::class, 'importForm'])->name('pegawai.import.form');
        Route::post('pegawai/import', [PegawaiController::class, 'importStore'])->name('pegawai.import.store');
        Route::get('pegawai/template', [PegawaiController::class, 'downloadTemplate'])->name('pegawai.template');
        Route::delete('pegawai/destroy-massal', [PegawaiController::class, 'destroyMassal'])->name('pegawai.destroy.massal');
        Route::patch('pegawai/{pegawai}/status', [PegawaiController::class, 'updateStatus'])->name('pegawai.status');
        Route::resource('pegawai', PegawaiController::class);
    });

    Route::middleware(['role:admin,operator'])->group(function () {
        Route::get('kelas-jabatan/import', [KelasJabatanController::class, 'importForm'])->name('kelas-jabatan.import.form');
        Route::post('kelas-jabatan/import', [KelasJabatanController::class, 'importStore'])->name('kelas-jabatan.import.store');
        Route::get('kelas-jabatan/template', [KelasJabatanController::class, 'downloadTemplate'])->name('kelas-jabatan.template');

        Route::resource('kelas-jabatan', KelasJabatanController::class);
    });

    Route::middleware(['role:super_admin,admin'])->group(function () {
        Route::get('/tpp/rekap', [TppController::class, 'rekap'])->name('tpp.rekap');
        Route::get('/tpp/rekap-sipd', [TppController::class, 'rekapSipd'])->name('tpp.rekap.sipd');
    });

    Route::middleware(['role:super_admin'])->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::post('/tpp/lock-period', [TppController::class, 'lockPeriod'])->name('tpp.lock-period');
        Route::post('/tpp/unlock-period', [TppController::class, 'unlockPeriod'])->name('tpp.unlock-period');
        Route::resource('unit-kerja', UnitKerjaController::class)->except(['show']);
    });
});

require __DIR__ . '/auth.php';
