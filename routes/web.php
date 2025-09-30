<?php

use Illuminate\Support\Facades\Route;

// Admin
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminNotifController;
use App\Http\Controllers\Admin\VerifikasiController;
use App\Http\Controllers\Admin\SkUkkController;
use App\Http\Controllers\Admin\SyaratController;
use App\Http\Controllers\Admin\ProfilAdminController;

// User
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\DokumenController;
use App\Http\Controllers\User\NotifikasiController;
use App\Http\Controllers\User\ProfileController;


// ======================
// Public
// ======================
Route::view('/', 'user.dashboard')->name('home');


// User atau koperasi
// Kalau mau pakai verifikasi, bisa pakai ini -> Route::middleware(['auth','verified'])->group(function ()
Route::middleware(['auth', 'role.user'])->group(function () {
    Route::get('/koperasi', [UserController::class, 'index'])->name('user.dashboard');

    // Profil
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Pengajuan Dokumen
    Route::get('/pengajuan', [DokumenController::class, 'create'])->name('user.create');
    Route::post('/pengajuan', [DokumenController::class, 'store'])->name('user.store');
    Route::get('/lihat-berkas', [DokumenController::class, 'lihatBerkas'])->name('user.lihat_berkas');

    // Notifikasi User
    Route::get('/notifikasi', [NotifikasiController::class, 'notifikasiUser'])->name('user.notifikasi');
});

// ======================
// Admin / Dinas
// ======================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:admin'])->group(function () {
    // Dashboard Admin
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // ---- Syarat (admin.syarat.*)
    Route::prefix('syarat')->name('syarat.')->group(function () {
        Route::get('/', [SyaratController::class, 'lihatSyarat'])->name('lihat_syarat');
        Route::get('/tambah', [SyaratController::class, 'tambahSyarat'])->name('tambah_syarat');
        Route::post('/tambah', [SyaratController::class, 'postTambahSyarat'])->name('post_tambah_syarat');
        Route::get('/{id}/edit', [SyaratController::class, 'editSyarat'])->name('edit_syarat');
        Route::post('/{id}/edit', [SyaratController::class, 'postEditSyarat'])->name('post_edit_syarat');
        Route::get('/{id}/hapus', [SyaratController::class, 'hapusSyarat'])->name('hapus_syarat');
    });

    // ---- Verifikasi (admin.verif.*)
    Route::prefix('verifikasi')->name('verif.')->group(function () {
        Route::get('/daftar-pengajuan', [DokumenController::class, 'daftarPengajuan'])->name('daftar_pengajuan');
        Route::get('/skukk/{id}', [VerifikasiController::class, 'verifBerkas'])->name('verif_berkas');
        Route::post('/skukk/{id}', [VerifikasiController::class, 'postVerifBerkas'])->name('post_verif_berkas');
        Route::get('/hasil', [VerifikasiController::class, 'hasilVerifikasi'])->name('hasil_verifikasi');
    });

    // ---- Berkas Admin
    Route::resource('skukk', SkUkkController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('skukk/{id}/download', [SkUkkController::class, 'download'])->name('skukk.download');

    // ---- Notifikasi Admin
    Route::get('/notifikasi-admin', [AdminNotifController::class, 'notifikasiAdmin'])->name('notifications.index');

    // ---- Profil Admin
    Route::get('/profil-admin', [ProfilAdminController::class, 'profilAdmin'])->name('profil.index'); // lihat profil
});

require __DIR__ . '/auth.php';
