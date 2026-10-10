<div class="flex flex-1 flex-col h-full overflow-hidden">
    {{-- ========================================================= --}}
    {{-- WINDOWS 11 HEADER & BREADCRUMB --}}
    {{-- ========================================================= --}}
    <header class="flex shrink-0 items-center justify-between border-b border-neutral-200/90 dark:border-white/5 bg-white/70 dark:bg-[#2b2b2b]/70 px-6 py-3.5 backdrop-blur-xl">
        <div>
            {{-- Windows 11 Breadcrumb --}}
            <div class="flex items-center gap-1.5 text-[11px] font-medium text-neutral-500 dark:text-neutral-400">
                <span>{{ $this->t('app_title') }}</span>
                <flux:icon name="chevron-right" class="size-3 text-neutral-400" />
                <span class="text-neutral-700 dark:text-neutral-300">{{ $this->t('crumb_programs') }}</span>
            </div>

            <div class="flex items-center gap-2 mt-0.5">
                <h1 class="text-base font-semibold tracking-tight text-neutral-900 dark:text-white">{{ $this->t('header_installed_apps') }}</h1>
                <span class="rounded-md bg-neutral-200/70 dark:bg-white/10 px-2 py-0.2 text-[11px] font-medium text-neutral-600 dark:text-neutral-300">{{ $stats['total'] }}</span>
            </div>
        </div>

        {{-- Primary Action (Install ZIP Button) --}}
        <div class="flex items-center gap-2">
            <button
                type="button"
                wire:click="openUploadModal"
                style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                class="flex items-center gap-2 rounded-lg px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs transition-all hover:brightness-110 active:scale-98"
            >
                <flux:icon name="arrow-up-tray" class="size-4" />
                <span>{{ $this->t('btn_install_zip') }}</span>
            </button>
        </div>
    </header>

    {{-- ========================================================= --}}
    {{-- TOOLBAR: COMMAND BAR (SEARCH & SEGMENTED TABS) --}}
    {{-- ========================================================= --}}
    <div class="flex shrink-0 flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 px-6 pt-4 pb-2.5">
        {{-- Search Input --}}
        <div class="relative w-full sm:w-80">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <flux:icon name="magnifying-glass" class="size-3.5 text-neutral-400 dark:text-neutral-500" />
            </div>
            <input
                type="text"
                wire:model.live.debounce.250ms="search"
                placeholder="{{ $this->t('search_placeholder') }}"
                class="w-full rounded-md border border-neutral-300/90 dark:border-white/10 bg-white dark:bg-[#2d2d2d] py-1.5 pl-9 pr-8 text-xs text-neutral-800 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 shadow-xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
            />
            @if ($search)
                <button
                    type="button"
                    wire:click="$set('search', '')"
                    class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-neutral-400 hover:text-neutral-700 dark:hover:text-white transition-colors"
                >
                    <flux:icon name="x-mark" class="size-3.5" />
                </button>
            @endif
        </div>

        {{-- Filter Segmented Controls --}}
        <div class="flex items-center gap-1 rounded-md bg-neutral-200/60 dark:bg-[#2d2d2d] p-1 border border-neutral-300/60 dark:border-white/10 text-xs">
            <button
                type="button"
                wire:click="setTab('all')"
                @if ($activeTab === 'all' || $activeTab === 'installed')
                    style="background-color: var(--accent-color, {{ $accent['hex'] }}); color: #ffffff;"
                @endif
                class="flex items-center gap-1.5 rounded-[5px] px-3 py-1 font-medium transition-all {{ ($activeTab === 'all' || $activeTab === 'installed') ? 'text-white shadow-xs' : 'text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white' }}"
            >
                <span>{{ $this->t('tab_all') }}</span>
                <span class="rounded px-1.5 py-0.2 text-[10px] {{ ($activeTab === 'all' || $activeTab === 'installed') ? 'bg-white/20 text-white' : 'bg-neutral-300/80 text-neutral-600 dark:bg-white/10 dark:text-neutral-400' }}">{{ $stats['total'] }}</span>
            </button>

            <button
                type="button"
                wire:click="setTab('custom')"
                @if ($activeTab === 'custom')
                    style="background-color: var(--accent-color, {{ $accent['hex'] }}); color: #ffffff;"
                @endif
                class="flex items-center gap-1.5 rounded-[5px] px-3 py-1 font-medium transition-all {{ $activeTab === 'custom' ? 'text-white shadow-xs' : 'text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white' }}"
            >
                <span>{{ $this->t('tab_custom') }}</span>
                <span class="rounded px-1.5 py-0.2 text-[10px] {{ $activeTab === 'custom' ? 'bg-white/20 text-white' : 'bg-neutral-300/80 text-neutral-600 dark:bg-white/10 dark:text-neutral-400' }}">{{ $stats['custom'] }}</span>
            </button>

            <button
                type="button"
                wire:click="setTab('system')"
                @if ($activeTab === 'system')
                    style="background-color: var(--accent-color, {{ $accent['hex'] }}); color: #ffffff;"
                @endif
                class="flex items-center gap-1.5 rounded-[5px] px-3 py-1 font-medium transition-all {{ $activeTab === 'system' ? 'text-white shadow-xs' : 'text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white' }}"
            >
                <span>{{ $this->t('tab_system') }}</span>
                <span class="rounded px-1.5 py-0.2 text-[10px] {{ $activeTab === 'system' ? 'bg-white/20 text-white' : 'bg-neutral-300/80 text-neutral-600 dark:bg-white/10 dark:text-neutral-400' }}">{{ $stats['system'] }}</span>
            </button>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- INSTALLED APPS (WINDOWS 11 FLUENT LIST TILES) --}}
    {{-- ========================================================= --}}
    <div class="flex-1 overflow-y-auto px-6 pb-4">
        <div class="flex flex-col gap-1.5">
            @forelse ($applications as $app)
                <div class="rounded-xl border border-neutral-200/90 dark:border-white/5 bg-white dark:bg-[#2b2b2b]/70 shadow-2xs transition-all hover:border-neutral-300 dark:hover:border-white/10 group overflow-hidden">
                    {{-- Accordion Header / Primary Row --}}
                    <div class="flex items-center justify-between gap-2 p-3 transition-colors {{ $expandedApp === $app['id'] ? 'bg-neutral-50/90 dark:bg-white/[0.03] border-b border-neutral-200/60 dark:border-white/5' : 'hover:bg-neutral-50/60 dark:hover:bg-[#323232]' }}">
                        {{-- Left: Icon & Application Name / Details (Click to Toggle Accordion) --}}
                        <div
                            wire:click="toggleAppDetails('{{ $app['id'] }}')"
                            class="flex items-center gap-3.5 min-w-0 flex-1 cursor-pointer select-none"
                            title="{{ $this->t('app_accordion_title') }}"
                        >
                            {{-- Accordion Chevron Indicator --}}
                            <div class="text-neutral-400 dark:text-neutral-500 transition-transform duration-200 {{ $expandedApp === $app['id'] ? 'rotate-90 text-neutral-700 dark:text-neutral-200' : '' }}">
                                <flux:icon name="chevron-right" class="size-3.5" />
                            </div>

                            <x-minios.icon :name="$app['icon']" class="size-9 shrink-0" />

                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-xs text-neutral-900 dark:text-white truncate">{{ $app['name'] }}</span>
                                    <span class="rounded bg-neutral-100 dark:bg-black/30 border border-neutral-200/60 dark:border-white/5 px-1.5 py-0.2 text-[10px] text-neutral-500 dark:text-neutral-400 font-mono">{{ $app['id'] }}</span>
                                </div>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-neutral-500 dark:text-neutral-400">
                                    <span>{{ $app['size'] }}</span>
                                    <span>•</span>
                                    <span>v{{ $app['version'] }}</span>
                                    @if (! empty($app['models']) || ! empty($app['migrations']))
                                        <span>•</span>
                                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium">
                                            <flux:icon name="server-stack" class="size-3" />
                                            <span>{{ $this->t('models_migrations_count', ['models' => count($app['models']), 'migrations' => count($app['migrations'])]) }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Middle: Type Badge (System or Custom) --}}
                        <div class="flex items-center gap-2 shrink-0">
                            @if ($app['isCore'])
                                <flux:badge size="sm" icon="shield-check" icon:variant="outline">
                                    {{ $this->t('badge_system') }}
                                </flux:badge>
                            @else
                                <flux:badge size="sm" icon="cube" icon:variant="outline" color="emerald">
                                    {{ $this->t('badge_custom') }}
                                </flux:badge>
                            @endif
                        </div>

                        {{-- Right: Actions --}}
                        <div class="flex items-center gap-1.5 shrink-0">
                            {{-- Open in MiniOS Desktop Window --}}
                            <flux:button
                                type="button"
                                @click="openWindow('{{ $app['id'] }}')"
                                title="{{ $this->t('btn_open_title') }}"
                                size="sm"
                            >
                                <flux:icon name="arrow-top-right-on-square" class="size-3.5" />
                                <span>{{ $this->t('btn_open') }}</span>
                            </flux:button>

                            {{-- Uninstall Custom App --}}
                            @if ($app['canUninstall'])
                                <button
                                    type="button"
                                    wire:click="confirmUninstall('{{ $app['id'] }}')"
                                    title="{{ $this->t('btn_uninstall_title') }}"
                                    class="inline-flex items-center gap-1 rounded-md border border-rose-200/90 dark:border-rose-500/20 bg-rose-50 dark:bg-rose-500/10 px-3 py-1.5 text-xs font-medium text-rose-700 dark:text-rose-300 shadow-xs transition-all hover:bg-rose-600 hover:text-white hover:border-transparent active:scale-98"
                                >
                                    <flux:icon name="trash" class="size-3.5" />
                                    <span>{{ $this->t('btn_uninstall') }}</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Accordion Body / Expandable Detail Section --}}
                    @if ($expandedApp === $app['id'])
                        <div class="border-t border-neutral-200/70 dark:border-white/5 bg-neutral-50/40 dark:bg-black/20 p-4 space-y-4 animate-in fade-in slide-in-from-top-1 duration-150">
                            {{-- Info Grid (Manifest, Paths, Entrypoint) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                <div class="rounded-lg border border-neutral-200/80 dark:border-white/5 bg-white dark:bg-[#202020] p-3 space-y-2">
                                    <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">{{ $this->t('arch_components') }}</div>
                                    <div class="space-y-1 text-[11px]">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-neutral-500 shrink-0">{{ $this->t('lbl_class_manifest') }}</span>
                                            <span class="font-mono text-neutral-800 dark:text-neutral-200 text-right truncate" title="{{ $app['class'] }}">{{ $app['class'] }}</span>
                                        </div>
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-neutral-500 shrink-0">{{ $this->t('lbl_livewire_component') }}</span>
                                            <span class="font-mono text-neutral-800 dark:text-neutral-200 text-right truncate" title="{{ $app['component'] ?? '-' }}">{{ $app['component'] ?? '-' }}</span>
                                        </div>
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-neutral-500 shrink-0">{{ $this->t('lbl_entry_route') }}</span>
                                            <span class="font-mono text-neutral-800 dark:text-neutral-200 text-right">{{ $app['entry'] }}</span>
                                        </div>
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-neutral-500 shrink-0">{{ $this->t('lbl_installed_at') }}</span>
                                            <span class="text-neutral-800 dark:text-neutral-200 text-right">{{ $app['installedAt'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-neutral-200/80 dark:border-white/5 bg-white dark:bg-[#202020] p-3 space-y-2">
                                    <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">{{ $this->t('registered_routes') }}</div>
                                    <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto pr-1">
                                        @forelse ($app['routes'] as $route)
                                            <span class="inline-flex items-center rounded bg-neutral-100 dark:bg-white/5 px-2 py-0.5 font-mono text-[10px] text-neutral-600 dark:text-neutral-300 border border-neutral-200/60 dark:border-white/5">
                                                {{ $route }}
                                            </span>
                                        @empty
                                            <span class="text-[11px] text-neutral-400 italic">{{ $this->t('no_custom_routes') }}</span>
                                        @endforelse
                                    </div>
                                    @if ($app['folderPath'])
                                        <div class="pt-1 border-t border-neutral-100 dark:border-white/5">
                                            <div class="text-[10px] text-neutral-400 truncate" title="{{ $app['folderPath'] }}">
                                                <span class="font-medium text-neutral-500">{{ $this->t('lbl_folder') }}</span> {{ $app['folderPath'] }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Database Migrations & Models Section --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                {{-- Migrations Box --}}
                                <div class="rounded-lg border border-neutral-200/80 dark:border-white/5 bg-white dark:bg-[#202020] p-3 space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <flux:icon name="circle-stack" class="size-3.5 text-neutral-400" />
                                            <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">{{ $this->t('db_migrations') }}</span>
                                        </div>
                                        <span class="rounded-full bg-neutral-100 dark:bg-white/10 px-2 py-0.2 text-[10px] font-medium text-neutral-600 dark:text-neutral-300">
                                            {{ $this->t('migrations_count', ['count' => count($app['migrations'])]) }}
                                        </span>
                                    </div>

                                    @if (! empty($app['migrations']))
                                        <div class="divide-y divide-neutral-100 dark:divide-white/5 max-h-36 overflow-y-auto">
                                            @foreach ($app['migrations'] as $m)
                                                <div class="flex items-center justify-between py-1.5 text-[11px] gap-2">
                                                    <span class="font-mono text-neutral-700 dark:text-neutral-300 truncate" title="{{ $m['name'] }}">
                                                        {{ $m['name'] }}
                                                    </span>
                                                    @if ($m['applied'])
                                                        <span class="inline-flex items-center gap-1 rounded bg-emerald-50 dark:bg-emerald-500/10 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-500/20 shrink-0">
                                                            <flux:icon name="check" class="size-2.5" />
                                                            <span>{{ $this->t('badge_applied') }}</span>
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 rounded bg-amber-50 dark:bg-amber-500/10 px-1.5 py-0.5 text-[10px] font-medium text-amber-700 dark:text-amber-400 border border-amber-200/80 dark:border-amber-500/20 shrink-0">
                                                            <span>{{ $this->t('badge_pending') }}</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="rounded border border-dashed border-neutral-200 dark:border-white/10 p-3 text-center text-[11px] text-neutral-400 italic">
                                            {{ $this->t('no_migrations') }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Eloquent Models Box --}}
                                <div class="rounded-lg border border-neutral-200/80 dark:border-white/5 bg-white dark:bg-[#202020] p-3 space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <flux:icon name="table-cells" class="size-3.5 text-neutral-400" />
                                            <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">{{ $this->t('eloquent_models') }}</span>
                                        </div>
                                        <span class="rounded-full bg-neutral-100 dark:bg-white/10 px-2 py-0.2 text-[10px] font-medium text-neutral-600 dark:text-neutral-300">
                                            {{ $this->t('models_count', ['count' => count($app['models'])]) }}
                                        </span>
                                    </div>

                                    @if (! empty($app['models']))
                                        <div class="divide-y divide-neutral-100 dark:divide-white/5 max-h-36 overflow-y-auto">
                                            @foreach ($app['models'] as $mod)
                                                <div class="flex items-center justify-between py-1.5 text-[11px] gap-2">
                                                    <div class="min-w-0">
                                                        <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $mod['name'] }}</span>
                                                        @if ($mod['table'])
                                                            <span class="text-neutral-400 font-mono text-[10px] ml-1.5">({{ $mod['table'] }})</span>
                                                        @endif
                                                    </div>
                                                    <div class="shrink-0 flex items-center gap-1.5">
                                                        @if ($mod['count'] !== null)
                                                            <span class="inline-flex items-center rounded bg-neutral-100 dark:bg-white/5 px-2 py-0.5 text-[10px] font-medium text-neutral-600 dark:text-neutral-300 border border-neutral-200/60 dark:border-white/5">
                                                                {{ $this->t('data_count', ['count' => $mod['count']]) }}
                                                            </span>
                                                        @endif
                                                        <span class="inline-flex items-center gap-0.5 rounded bg-emerald-50 dark:bg-emerald-500/10 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-500/20">
                                                            {{ $this->t('badge_ready') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="rounded border border-dashed border-neutral-200 dark:border-white/10 p-3 text-center text-[11px] text-neutral-400 italic">
                                            {{ $this->t('no_models') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Required Packages & Dependencies Section --}}
                            <div class="rounded-lg border border-neutral-200/80 dark:border-white/5 bg-white dark:bg-[#202020] p-3 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <flux:icon name="archive-box" class="size-3.5 text-neutral-400" />
                                        <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">{{ $this->t('composer_dependencies') }}</span>
                                    </div>
                                    <span class="rounded-full bg-neutral-100 dark:bg-white/10 px-2 py-0.2 text-[10px] font-medium text-neutral-600 dark:text-neutral-300">
                                        {{ $this->t('dependencies_count', ['count' => count($app['packages'] ?? [])]) }}
                                    </span>
                                </div>

                                @if (! empty($app['packages']))
                                    <div class="divide-y divide-neutral-100 dark:divide-white/5 max-h-48 overflow-y-auto">
                                        @foreach ($app['packages'] as $pkg)
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between py-2 text-[11px] gap-2">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-mono font-medium text-neutral-900 dark:text-neutral-100">{{ $pkg['name'] }}</span>
                                                        @if ($pkg['installed'] && $pkg['version'])
                                                            <span class="rounded bg-neutral-100 dark:bg-white/5 px-1.5 py-0.2 text-[10px] font-mono text-neutral-500">{{ $pkg['version'] }}</span>
                                                        @endif
                                                    </div>
                                                    @if (! $pkg['installed'])
                                                        <div class="flex items-center gap-1.5 mt-1">
                                                            <span class="text-[10px] text-neutral-400">{{ $this->t('lbl_install_cmd') }}</span>
                                                            <code class="rounded bg-neutral-100 dark:bg-black/40 px-1.5 py-0.5 font-mono text-[10px] text-neutral-700 dark:text-neutral-300 border border-neutral-200/60 dark:border-white/5 select-all">{{ $pkg['command'] }}</code>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="shrink-0 flex items-center gap-2">
                                                    @if ($pkg['installed'])
                                                        <span class="inline-flex items-center gap-1 rounded bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-500/20">
                                                            <flux:icon name="check" class="size-2.5" />
                                                            <span>{{ $this->t('badge_installed') }}</span>
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 rounded bg-rose-50 dark:bg-rose-500/10 px-2 py-0.5 text-[10px] font-medium text-rose-700 dark:text-rose-400 border border-rose-200/80 dark:border-rose-500/20">
                                                            <flux:icon name="exclamation-triangle" class="size-2.5" />
                                                            <span>{{ $this->t('badge_uninstalled') }}</span>
                                                        </span>

                                                        <button
                                                            type="button"
                                                            wire:click="openComposerModal('{{ $app['id'] }}', '{{ $pkg['name'] }}')"
                                                            class="inline-flex items-center gap-1 rounded bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white px-2 py-0.5 text-[10px] font-medium shadow-sm transition"
                                                        >
                                                            <flux:icon name="bolt" class="size-2.5" />
                                                            <span>{{ $this->t('btn_install_gui') }}</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="rounded border border-dashed border-neutral-200 dark:border-white/10 p-3 text-center text-[11px] text-neutral-400 italic">
                                        {{ $this->t('no_dependencies') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-xl border border-neutral-200/90 dark:border-white/5 bg-white dark:bg-[#2b2b2b]/70 py-12 text-center">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-neutral-100 dark:bg-white/5 text-neutral-400">
                            <flux:icon name="magnifying-glass" class="size-6" />
                        </div>
                        <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $this->t('no_apps_found_title') }}</p>
                        <p class="text-xs text-neutral-500">{{ $this->t('no_apps_found_desc') }}</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- BOTTOM STATUS BAR --}}
    {{-- ========================================================= --}}
    <footer class="flex shrink-0 items-center justify-between border-t border-neutral-200/80 dark:border-white/5 bg-[#ebebeb]/90 dark:bg-[#1f1f1f]/95 px-5 py-2 text-[11px] text-neutral-500 dark:text-neutral-400 backdrop-blur-md">
        <div class="flex items-center gap-2 min-w-0">
            @if ($statusMessage)
                <div class="flex items-center gap-2 truncate animate-in fade-in duration-150">
                    @if ($statusType === 'success')
                        <span class="flex size-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                        <span class="font-medium text-emerald-600 dark:text-emerald-400 truncate">{{ $statusMessage }}</span>
                    @else
                        <span class="flex size-2 rounded-full bg-rose-500 animate-pulse shrink-0"></span>
                        <span class="font-medium text-rose-600 dark:text-rose-400 truncate">{{ $statusMessage }}</span>
                    @endif
                    <button
                        type="button"
                        wire:click="dismissStatus"
                        title="{{ $this->t('btn_close') }}"
                        class="ml-1 text-neutral-400 hover:text-neutral-700 dark:hover:text-white transition-colors"
                    >
                        <flux:icon name="x-mark" class="size-3" />
                    </button>
                </div>
            @else
                <div class="flex items-center gap-2 truncate">
                    <span class="inline-block size-1.5 rounded-full bg-neutral-400 dark:bg-neutral-600 shrink-0"></span>
                    <span class="truncate">
                        {{ $this->t('total_apps_status', ['total' => $stats['total'], 'system' => $stats['system'], 'custom' => $stats['custom']]) }}
                    </span>
                </div>
            @endif
        </div>

        <div class="shrink-0 font-medium text-neutral-400 dark:text-neutral-500 text-[11px]">
            <span>{{ $this->t('storage_size', ['size' => $stats['storage']]) }}</span>
        </div>
    </footer>
</div>
