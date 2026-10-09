<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-font="{{ os_setting()->get('appearance.font_family', 'inter') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#201a1e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>{{ $title ?? 'MiniOS' }}</title>
    <link rel="manifest" href="{{ asset('minios/favicon/site.webmanifest') }}">
    <link rel="icon" href="{{ asset('minios/favicon/favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('minios/favicon/favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('minios/favicon/apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">

    @fonts
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="h-dvh w-screen overflow-hidden bg-white text-neutral-900 dark:bg-black dark:text-white font-sans antialiased">
    {{ $slot }}

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => {
                    console.log('SW registration failed: ', err);
                });
            });
        }
    </script>

    {{-- app.js bundles and starts Livewire/Alpine. Emit config, not a second runtime. --}}
    @livewireScriptConfig
    @fluxScripts
</body>
</html>
