<?php

use App\Http\Controllers\Webhooks\TelegramWebhookController;
use App\Http\Controllers\Webhooks\WhatsAppWebhookController;
use App\Http\Middleware\VerifyTelegramSecret;
use App\Http\Middleware\VerifyWhatsAppSignature;
use Illuminate\Support\Facades\Route;

Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify'])->name('webhooks.whatsapp.verify');
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'store'])
    ->middleware(VerifyWhatsAppSignature::class)
    ->name('webhooks.whatsapp.store');

Route::post('/webhooks/telegram', [TelegramWebhookController::class, 'store'])
    ->middleware(VerifyTelegramSecret::class)
    ->name('webhooks.telegram.store');
