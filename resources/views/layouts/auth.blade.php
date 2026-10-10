@php
    $currentWallpaper = os_setting()->get('appearance.wallpaper', 'wall-1');
    $wallpaperFile = str_ends_with($currentWallpaper, '.webp') ? $currentWallpaper : $currentWallpaper.'.webp';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#201a1e">

    <title>{{ isset($title) && $title ? $title . ' - MiniOS' : 'MiniOS' }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @fonts
    @livewireStyles
    @miniosStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body 
    x-data="{
        lockTime: '',
        lockDate: '',
        updateLockClock() {
            const now = new Date();
            this.lockTime = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).format(now);
            this.lockDate = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }).format(now);
        }
    }"
    x-init="
        updateLockClock();
        setInterval(() => updateLockClock(), 1000);
    "
    style="background-image: url('{{ asset('minios/wallpapers/'.$wallpaperFile) }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"
    class="fixed inset-0 z-[99999] flex flex-col items-center justify-center overflow-y-auto p-4 sm:p-6 text-white select-none font-sans antialiased"
>
    {{-- Glassmorphism Blur Overlay --}}
    <div class="fixed inset-0 bg-neutral-950/60 backdrop-blur-2xl"></div>

    {{-- Main Content Layer (Perfect Middle-Center) --}}
    <div class="relative z-10 flex min-h-full w-full flex-col items-center justify-center py-12">
        <div class="my-auto flex w-full max-w-xs flex-col items-center text-center">
            {{ $slot }}
        </div>
    </div>

    {{-- Bottom Section: System Footer --}}
    <div class="fixed bottom-4 inset-x-0 z-20 text-center text-xs text-white/50 pointer-events-none">
        <span>MiniOS (Desktop Environment)</span>
    </div>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @livewireScriptConfig
    @fluxScripts
    @miniosScripts
</body>
</html>
