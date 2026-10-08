<?php

use Illuminate\Support\Facades\Route;
use Novay\MiniOS\Http\Middleware\EnsureDesktopNotLocked;
use Novay\MiniOS\Livewire\Desktop;
use Novay\MiniOS\Livewire\LockScreen;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('lock', LockScreen::class)->name('lock');
});

Route::middleware(['web', 'auth', 'verified', EnsureDesktopNotLocked::class])->group(function () {
    Route::get('/', Desktop::class)->name('home');
    Route::get('{desktopPath?}', Desktop::class)
        ->where('desktopPath', '.*')
        ->name('desktop');
});
