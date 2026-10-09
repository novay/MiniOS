<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Novay\MiniOS\Http\Middleware\EnsureDesktopNotLocked;
use Novay\MiniOS\Http\Middleware\SetDesktopLocale;
use Novay\MiniOS\Livewire\Desktop;
use Novay\MiniOS\Livewire\LockScreen;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('lock', LockScreen::class)->name('lock');
});

$desktopMiddleware = config('minios.middleware') ?? array_values(array_filter([
    'web',
    'auth',
    (class_exists(Features::class) && in_array(Features::emailVerification(), config('fortify.features', []))) ? 'verified' : null,
    EnsureDesktopNotLocked::class,
    SetDesktopLocale::class,
]));

Route::middleware($desktopMiddleware)->group(function () {
    Route::get('/', Desktop::class)->name('home');
    Route::get('{desktopPath?}', Desktop::class)
        ->where('desktopPath', '.*')
        ->name('desktop');
});
