<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChannelMessageController;
use App\Http\Controllers\DmController;
use App\Http\Controllers\DmMessageController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Mensajes en canales
    Route::get('/channels/{channel}/messages', [ChannelMessageController::class, 'index'])
        ->name('channels.messages.index');
    Route::post('/channels/{channel}/messages', [ChannelMessageController::class, 'store'])
        ->name('channels.messages.store');

    // Rutas para hilos (threads)
    Route::get('/threads/{message}', [\App\Http\Controllers\ThreadController::class, 'show'])->name('threads.show');
    Route::post('/threads/{message}/reply', [\App\Http\Controllers\ThreadController::class, 'reply'])->name('threads.reply');

    // Rutas de mensajes directos
    Route::get('/dm/{user}', [DmController::class, 'show'])->name('dm.show');
    Route::get('/dm/{dmGroup}/messages', [DmMessageController::class, 'index'])
        ->name('dm.messages.index');
    Route::post('/dm/{dmGroup}/messages', [DmMessageController::class, 'store'])
        ->name('dm.messages.store');
});
