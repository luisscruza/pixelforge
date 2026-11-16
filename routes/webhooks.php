<?php

declare(strict_types=1);

use App\Http\Controllers\PixelforgeWebhookController;
use App\Http\Middleware\VerifyPixelforgeSignature;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/pixelforge', [PixelforgeWebhookController::class, 'store'])
    ->middleware(VerifyPixelforgeSignature::class)
    ->name('webhooks.pixelforge.store');
