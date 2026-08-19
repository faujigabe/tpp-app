<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KelasJabatanController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TppController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/tpp', [TppController::class, 'index'])->name('tpp.index');

    Route::middleware(['role:admin,operator,viewer'])->group(function () {
        Route::get('/tpp/cetak', [TppController::class, 'cetak'])->name('tpp.cetak');
        Route::get('/tpp/export', [TppController::class, 'exportExcel'])->name('tpp.export');
    });

    Route::middleware(['role:admin,operator'])->group(function () {
        Route::get('/tpp/create', [TppController::class, 'create'])->name('tpp.create');
        Route::post('/tpp/store', [TppController::class, 'store'])->name('tpp.store');
        Route::get('/tpp/edit-massal', [TppController::class, 'editMassal'])->name('tpp.edit.massal');
        Route::post('/tpp/update-massal', [TppController::class, 'updateMassal'])->name('tpp.update.massal');
        Route::delete('/tpp/destroy-massal', [TppController::class, 'destroyMassal'])->name('tpp.destroy.massal');
        Route::delete('/tpp/{tpp}', [TppController::class, 'destroy'])->name('tpp.destroy');
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::get('pegawai/import', [PegawaiController::class, 'importForm'])->name('pegawai.import.form');
        Route::post('pegawai/import', [PegawaiController::class, 'importStore'])->name('pegawai.import.store');
        Route::get('pegawai/template', [PegawaiController::class, 'downloadTemplate'])->name('pegawai.template');
        Route::delete('pegawai/destroy-massal', [PegawaiController::class, 'destroyMassal'])->name('pegawai.destroy.massal');

        Route::get('kelas-jabatan/import', [KelasJabatanController::class, 'importForm'])->name('kelas-jabatan.import.form');
        Route::post('kelas-jabatan/import', [KelasJabatanController::class, 'importStore'])->name('kelas-jabatan.import.store');
        Route::get('kelas-jabatan/template', [KelasJabatanController::class, 'downloadTemplate'])->name('kelas-jabatan.template');

        Route::resource('pegawai', PegawaiController::class);
        Route::resource('kelas-jabatan', KelasJabatanController::class);
        Route::resource('users', UserController::class);

        Route::get('/tpp/{tpp}/edit', [TppController::class, 'edit'])->name('tpp.edit');
        Route::put('/tpp/{tpp}', [TppController::class, 'update'])->name('tpp.update');
        Route::get('/tpp/rekap', [TppController::class, 'rekap'])->name('tpp.rekap');
    });
});

require __DIR__ . '/auth.php';
