<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\SpendReportController;
use App\Http\Controllers\CreditPurchaseController;
use App\Http\Controllers\GenerationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

Route::get('/', fn (): Response => Inertia::render('Welcome'))->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::redirect('dashboard', '/generations')->name('dashboard');

    Route::get('generations', [GenerationController::class, 'index'])->name('generations.index');
    Route::post('generations', [GenerationController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('generations.store');

    Route::resource('credit-purchases', CreditPurchaseController::class)->only(['store']);

    Route::get('admin/spend-report', [SpendReportController::class, 'index'])
        ->middleware('can:viewSpendReport')
        ->name('admin.spend-report.index');
});

require __DIR__.'/settings.php';
