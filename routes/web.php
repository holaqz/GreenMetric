<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\CycleController;
use App\Http\Controllers\IndicatorResponseController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\IndicatorExportController;
use App\Http\Controllers\UserAccessController;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Редирект с /dashboard на /cycles (для обратной совместимости ссылок)
    Route::redirect('dashboard', '/cycles')->name('dashboard');

    // ===== Cycle Management =====
    Route::get('/cycles', [CycleController::class, 'index'])->name('cycles.index');
    Route::post('/cycles', [CycleController::class, 'store'])->name('cycles.store');
    Route::get('/cycles/{cycle}', [CycleController::class, 'show'])->name('cycles.show');
    Route::patch('/cycles/{cycle}/status', [CycleController::class, 'updateStatus'])->name('cycles.update-status');

    // ===== Indicator Responses =====
    Route::patch('/responses/{response}', [IndicatorResponseController::class, 'update'])->name('responses.update');
    Route::post('/responses/{response}/files', [IndicatorResponseController::class, 'uploadFile'])->name('responses.files.upload');
    Route::post('/responses/{response}/links', [IndicatorResponseController::class, 'addLink'])->name('responses.links.add');
    Route::get('/responses/{response}/history', [IndicatorResponseController::class, 'history'])->name('responses.history');

    // ===== Files =====
    Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::get('/files/{file}/view', [FileController::class, 'show'])->name('files.show');
    Route::delete('/files/{file}', [IndicatorResponseController::class, 'deleteFile'])->name('files.delete');
    Route::patch('/files/{file}/description', [FileController::class, 'updateDescription'])->name('files.update-description');

    // ===== Reports Export (Word) =====
    Route::get('/cycles/{cycle}/export/{category}/word', [ReportController::class, 'exportWord'])->name('cycles.export.word');
    Route::get('/cycles/{cycle}/export/{category}/evidence', [ReportController::class, 'exportEvidenceZip'])->name('cycles.export.evidence');
    
    // ===== Individual Indicator Export (Word) =====
    Route::get('/cycles/{cycle}/indicators/{indicator}/export', [IndicatorExportController::class, 'exportIndicator'])->name('indicators.export');
    
    // ===== User Access Management (Admin only) =====
    Route::get('/settings/user-access', [UserAccessController::class, 'index'])->name('settings.user-access');
    Route::post('/settings/user-access/{user}', [UserAccessController::class, 'update'])->name('settings.user-access.update');
});

require __DIR__.'/settings.php';
