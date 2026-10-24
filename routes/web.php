<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\OptInController;
use App\Http\Controllers\ReplyBackController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\VoteController;
use App\Http\Middleware\AssignNewsroom;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware(['auth', 'verified', AssignNewsroom::class])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
    Route::post('campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::get('campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
    Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');
    Route::post('campaigns/{campaign}/test-send', [CampaignController::class, 'testSend'])->name('campaigns.test-send');
    Route::post('campaigns/{campaign}/simulate', [CampaignController::class, 'simulate'])->name('campaigns.simulate');

    Route::get('campaigns/{campaign}/inbox', [ReplyController::class, 'index'])->name('campaigns.inbox');
    Route::patch('campaigns/{campaign}/messages/{message}/transcript', [ReplyController::class, 'updateTranscript'])->name('messages.transcript.update');
    Route::get('campaigns/{campaign}/messages/{message}/audio', [ReplyController::class, 'audio'])->name('messages.audio');

    Route::get('campaigns/{campaign}/topics', [TopicController::class, 'index'])->name('campaigns.topics');
    Route::patch('campaigns/{campaign}/topics/{topic}', [TopicController::class, 'update'])->name('topics.update');
    Route::post('campaigns/{campaign}/topics/merge', [TopicController::class, 'merge'])->name('topics.merge');
    Route::post('campaigns/{campaign}/recluster', [TopicController::class, 'recluster'])->name('campaigns.recluster');

    Route::get('campaigns/{campaign}/votes', [VoteController::class, 'show'])->name('campaigns.votes');
    Route::post('campaigns/{campaign}/votes', [VoteController::class, 'store'])->name('campaigns.votes.store');

    Route::get('campaigns/{campaign}/reply-back', [ReplyBackController::class, 'create'])->name('campaigns.reply-back');
    Route::post('campaigns/{campaign}/reply-back', [ReplyBackController::class, 'store'])->name('campaigns.reply-back.store');

    Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('contacts/export', [ContactController::class, 'export'])->name('contacts.export');
    Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');

    Route::get('opt-in', [OptInController::class, 'show'])->name('opt-in');
    Route::get('opt-in/qr.svg', [OptInController::class, 'qr'])->name('opt-in.qr');
    Route::get('metrics', [MetricsController::class, 'index'])->name('metrics');
});

require __DIR__.'/settings.php';
