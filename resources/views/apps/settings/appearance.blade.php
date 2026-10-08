<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
    <div class="space-y-0.5">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="#" class="text-xs">Pengaturan</flux:breadcrumbs.item>
            <flux:breadcrumbs.item class="text-xs">Tampilan &amp; Personalisasi</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <h1 class="text-lg font-bold tracking-tight text-neutral-900 dark:text-white">
            Tampilan &amp; Personalisasi
        </h1>
        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
            Atur tema antarmuka, warna aksen, font sistem, dan wallpaper desktop.
        </p>
    </div>

    <button
        type="button"
        wire:click="resetCategory('appearance')"
        class="self-start sm:self-auto flex items-center gap-2 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333333] transition-all active:scale-98"
    >
        <flux:icon name="arrow-path" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
        <span>Reset</span>
    </button>
</div>

<div class="mt-6 flex flex-col gap-5 max-w-3xl">
    {{-- Tema Antarmuka Card --}}
    <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs space-y-3">
        <div class="space-y-1">
            <div class="text-sm font-semibold text-neutral-900 dark:text-white">
                Tema Antarmuka
            </div>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                Pilih palet mode terang, gelap, atau otomatis mengikuti sistem perangkat.
            </p>
        </div>

        <div class="grid grid-cols-3 gap-3 pt-1">
            {{-- Mode Terang --}}
            <label class="flex cursor-pointer flex-col items-center justify-between rounded-xl border p-3.5 transition-all {{ ($appearance['theme'] ?? 'system') === 'light' ? ($accent['radio_card'] ?? 'border-indigo-500 bg-indigo-500/10 ring-1 ring-indigo-500') : 'border-neutral-200/90 dark:border-white/10 bg-neutral-50/70 dark:bg-white/5 hover:border-neutral-300 dark:hover:border-white/20' }}">
                <input type="radio" wire:model.live="appearance.theme" value="light" class="sr-only" />
                <flux:icon name="sun" class="size-10 text-amber-500" />
                <span class="mt-2.5 text-xs font-medium text-neutral-800 dark:text-neutral-200">
                    {{ __('Terang') }}
                </span>
            </label>

            {{-- Mode Gelap --}}
            <label class="flex cursor-pointer flex-col items-center justify-between rounded-xl border p-3.5 transition-all {{ ($appearance['theme'] ?? 'system') === 'dark' ? ($accent['radio_card'] ?? 'border-indigo-500 bg-indigo-500/10 ring-1 ring-indigo-500') : 'border-neutral-200/90 dark:border-white/10 bg-neutral-50/70 dark:bg-white/5 hover:border-neutral-300 dark:hover:border-white/20' }}">
                <input type="radio" wire:model.live="appearance.theme" value="dark" class="sr-only" />
                <flux:icon name="moon" class="size-10 text-sky-400" />
                <span class="mt-2.5 text-xs font-medium text-neutral-800 dark:text-neutral-200">Gelap</span>
            </label>

            {{-- Mode Sistem --}}
            <label class="flex cursor-pointer flex-col items-center justify-between rounded-xl border p-3.5 transition-all {{ ($appearance['theme'] ?? 'system') === 'system' ? ($accent['radio_card'] ?? 'border-indigo-500 bg-indigo-500/10 ring-1 ring-indigo-500') : 'border-neutral-200/90 dark:border-white/10 bg-neutral-50/70 dark:bg-white/5 hover:border-neutral-300 dark:hover:border-white/20' }}">
                <input type="radio" wire:model.live="appearance.theme" value="system" class="sr-only" />
                <flux:icon name="computer-desktop" class="size-10" />
                <span class="mt-2.5 text-xs font-medium text-neutral-800 dark:text-neutral-200">Sistem</span>
            </label>
        </div>
    </div>

    {{-- Warna Aksen Card --}}
    <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs space-y-3">
        <div class="space-y-1">
            <div class="text-sm font-semibold text-neutral-900 dark:text-white">
                Warna Aksen
            </div>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                Pilih warna sorotan untuk tombol, seleksi, dan elemen aktif di MiniOS.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-1">
            @php
                $accentColors = [
                    'zinc' => ['class' => 'bg-zinc-800 border border-zinc-600', 'hex' => '#27272a', 'label' => 'Zinc'],
                    'indigo' => ['class' => 'bg-indigo-600', 'hex' => '#6366f1', 'label' => 'Indigo'],
                    'emerald' => ['class' => 'bg-emerald-600', 'hex' => '#10b981', 'label' => 'Emerald'],
                    'sky' => ['class' => 'bg-sky-500', 'hex' => '#0ea5e9', 'label' => 'Sky'],
                    'amber' => ['class' => 'bg-amber-500', 'hex' => '#f59e0b', 'label' => 'Amber'],
                    'rose' => ['class' => 'bg-rose-600', 'hex' => '#f43f5e', 'label' => 'Rose'],
                    'violet' => ['class' => 'bg-violet-600', 'hex' => '#8b5cf6', 'label' => 'Violet'],
                ];
                $activeColorName = $appearance['accent_color'] ?? 'indigo';
            @endphp

            @foreach ($accentColors as $colorName => $colorInfo)
                @php
                    $isColorActive = $activeColorName === $colorName;
                @endphp
                <label class="group relative flex cursor-pointer items-center justify-center rounded-full p-1 ring-2 transition-all {{ $isColorActive ? 'ring-offset-2 ring-offset-white dark:ring-offset-[#2b2b2b]' : 'ring-transparent hover:scale-110' }}" style="{{ $isColorActive ? 'ring-color: '.$colorInfo['hex'].';' : '' }}">
                    <input type="radio" wire:model.live="appearance.accent_color" value="{{ $colorName }}" class="sr-only" />
                    <span class="size-7 rounded-full {{ $colorInfo['class'] }} shadow-xs flex items-center justify-center text-white">
                        @if ($isColorActive)
                            <flux:icon name="check" class="size-3.5 stroke-[3]" />
                        @endif
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Tipografi & Font Sistem Card --}}
    <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs space-y-3">
        <div class="space-y-1">
            <div class="text-sm font-semibold text-neutral-900 dark:text-white">
                Font Sistem
            </div>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                Pilih tipografi utama untuk desktop, bilah menu, dan seluruh jendela aplikasi.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-2.5 pt-1">
            @php
                $fonts = [
                    'inter' => [
                        'name' => 'Inter',
                        'desc' => 'Modern Crisp Sans-Serif',
                        'family' => "'Inter', sans-serif",
                        'tag' => 'Default',
                    ],
                    'san-francisco' => [
                        'name' => 'San Francisco',
                        'desc' => 'Apple macOS & iOS',
                        'family' => "-apple-system, BlinkMacSystemFont, 'SF Pro Display', sans-serif",
                        'tag' => 'Apple',
                    ],
                    'segoe' => [
                        'name' => 'Segoe UI',
                        'desc' => 'Microsoft Windows Fluent',
                        'family' => "'Segoe UI', Roboto, sans-serif",
                        'tag' => 'Windows',
                    ],
                    'ubuntu' => [
                        'name' => 'Ubuntu',
                        'desc' => 'Canonical Ubuntu Linux',
                        'family' => "'Ubuntu', sans-serif",
                        'tag' => 'Linux',
                    ],
                    'google' => [
                        'name' => 'Google',
                        'desc' => 'Google Sans & Roboto',
                        'family' => "'Roboto', 'Google Sans', sans-serif",
                        'tag' => 'Android',
                    ],
                    'system-ui' => [
                        'name' => 'System UI',
                        'desc' => 'Sistem Bawaan Perangkat',
                        'family' => 'system-ui, sans-serif',
                        'tag' => 'Native',
                    ],
                ];
                $currentFont = $appearance['font_family'] ?? 'inter';
            @endphp

            @foreach ($fonts as $fontKey => $fontData)
                @php
                    $isFontActive = $currentFont === $fontKey;
                @endphp
                <label class="group relative flex cursor-pointer items-center justify-between rounded-xl border p-3 transition-all {{ $isFontActive ? ($accent['radio_card'] ?? 'border-indigo-500 bg-indigo-500/10 ring-1 ring-indigo-500') : 'border-neutral-200/90 dark:border-white/10 bg-neutral-50/70 dark:bg-white/5 hover:border-neutral-300 dark:hover:border-white/20' }}">
                    <input type="radio" wire:model.live="appearance.font_family" value="{{ $fontKey }}" class="sr-only" />
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-neutral-200/80 dark:bg-white/10 text-neutral-800 dark:text-white font-semibold text-sm transition-transform group-hover:scale-105" style="font-family: {{ $fontData['family'] }};">
                            Aa
                        </div>
                        <div class="min-w-0 space-y-0.5">
                            <div class="flex items-center gap-1.5">
                                <span class="truncate text-sm font-semibold text-neutral-900 dark:text-neutral-100" style="font-family: {{ $fontData['family'] }};">{{ $fontData['name'] }}</span>
                            </div>
                            <p class="truncate text-xs text-neutral-500 dark:text-neutral-400">{{ $fontData['desc'] }}</p>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Wallpaper Latar Belakang Card --}}
    <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs space-y-3">
        <div class="space-y-1">
            <div class="text-sm font-semibold text-neutral-900 dark:text-white">
                Wallpaper Desktop
            </div>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                Pilih latar belakang visual untuk desktop MiniOS.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 pt-1">
            @foreach (range(1, 8) as $i)
                @php $wallKey = 'wall-'.$i; @endphp
                <label class="group relative flex aspect-video cursor-pointer overflow-hidden rounded-xl border transition-all {{ ($appearance['wallpaper'] ?? 'wall-1') === $wallKey ? 'ring-2 ring-offset-2 ring-offset-white dark:ring-offset-[#2b2b2b]' : 'border-neutral-200/90 dark:border-white/10 hover:border-neutral-300 dark:hover:border-white/20' }}" style="{{ ($appearance['wallpaper'] ?? 'wall-1') === $wallKey ? 'ring-color: var(--accent-color, '.$accent['hex'].');' : '' }}">
                    <input type="radio" wire:model.live="appearance.wallpaper" value="{{ $wallKey }}" class="sr-only" />
                    <img src="{{ asset('minios/wallpapers/wall-'.$i.'.webp') }}" alt="Wallpaper {{ $i }}" class="h-full w-full rounded-lg object-cover transition-transform duration-200 group-hover:scale-105" />
                    @if (($appearance['wallpaper'] ?? 'wall-1') === $wallKey)
                        <div class="absolute inset-0 flex items-center justify-center bg-black/40 backdrop-blur-[1px]">
                            <div class="flex size-6 items-center justify-center rounded-full text-white shadow-md" style="background-color: var(--accent-color, {{ $accent['hex'] }});">
                                <flux:icon name="check" class="size-3.5 stroke-[3]" />
                            </div>
                        </div>
                    @endif
                </label>
            @endforeach
        </div>
    </div>

    {{-- Efek Transparansi & Acrylic Blur Card --}}
    <div class="flex items-center justify-between rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs">
        <div class="space-y-1">
            <div class="text-sm font-semibold text-neutral-900 dark:text-white">
                Efek Transparansi &amp; Blur Panel
            </div>
            <div class="text-xs text-neutral-500 dark:text-neutral-400">
                Aktifkan efek kaca (Mica / Backdrop Blur) pada bilah atas dan header jendela.
            </div>
        </div>
        <flux:switch wire:model.live="appearance.panel_blur" />
    </div>
</div>
