<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Novay\MiniOS\Http\Middleware\EnsureDesktopNotLocked;
use Novay\MiniOS\Http\Middleware\SetDesktopLocale;
use Novay\MiniOS\Livewire\Desktop;
use Novay\MiniOS\Livewire\LockScreen;

$prefix = trim(config('minios.prefix', ''), '/');

Route::get('minios/assets/minios.js', [\Novay\MiniOS\Http\Controllers\AssetController::class, 'script'])->name('minios.assets.js');
Route::get('minios/assets/minios.css', [\Novay\MiniOS\Http\Controllers\AssetController::class, 'style'])->name('minios.assets.css');

Route::middleware(['web', 'auth'])->group(function () use ($prefix) {
    Route::get($prefix ? "{$prefix}/lock" : 'lock', LockScreen::class)->name('lock');
});

$desktopMiddleware = config('minios.middleware') ?? array_values(array_filter([
    'web',
    'auth',
    (class_exists(Features::class) && in_array(Features::emailVerification(), config('fortify.features', []))) ? 'verified' : null,
    EnsureDesktopNotLocked::class,
    SetDesktopLocale::class,
]));

Route::prefix($prefix)->middleware($desktopMiddleware)->group(function () {
    Route::get('/', Desktop::class)->name('home');
    Route::get('{desktopPath?}', Desktop::class)
        ->where('desktopPath', '.*')
        ->name('desktop');
});
