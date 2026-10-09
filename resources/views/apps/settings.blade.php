<div class="flex h-full min-h-130 w-full overflow-hidden bg-[#f3f3f3] dark:bg-[#202020] text-neutral-800 dark:text-neutral-100 font-sans select-none">
    {{-- ========================================================= --}}
    {{-- WINDOWS 11 FLUENT NAVIGATION SIDEBAR (LEFT) --}}
    {{-- ========================================================= --}}
    <aside class="flex w-64 md:w-72 shrink-0 flex-col border-r border-neutral-200/90 dark:border-white/5 bg-[#f8f8f8]/85 dark:bg-[#202020]/90 p-3.5 backdrop-blur-xl">
        {{-- User Profile Card (Windows 11 Settings Profile Card) --}}
        <div wire:click="setTab('account')" class="mb-3 flex cursor-pointer items-center gap-3 rounded-lg p-2.5 transition-all hover:bg-black/[0.04] dark:hover:bg-white/5 active:scale-98">
            <div class="flex size-11 items-center justify-center rounded-full text-white font-bold text-sm shadow-2xs shrink-0 transition-transform hover:scale-105" style="background-color: var(--accent-color, {{ $accent['hex'] }});">
                {{ auth()->user()?->initials() ?? 'US' }}
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="truncate text-sm font-semibold text-neutral-900 dark:text-white">{{ auth()->user()?->name ?? $this->t('default_user_name') }}</h2>
                <div class="flex items-center gap-1.5 mt-1">
                    <p class="truncate text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('local_account') }}</p>
                </div>
            </div>
        </div>

        {{-- Search Input (Windows 11 Fluent Search Box) --}}
        <div class="mb-3 px-0.5">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                    <flux:icon name="magnifying-glass" class="size-5 text-neutral-400 dark:text-neutral-500" />
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.150ms="search"
                    placeholder="{{ $this->t('search_placeholder') }}"
                    class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] py-1.5 pl-9 pr-7 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 shadow-2xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
                />
                @if ($search !== '')
                    <button
                        type="button"
                        wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2 text-neutral-400 hover:text-neutral-700 dark:hover:text-white"
                        title="{{ $this->t('search_clear_title') }}"
                    >
                        <flux:icon name="x-mark" class="size-3.5" />
                    </button>
                @endif
            </div>
        </div>

        {{-- Category Navigation List --}}
        <nav class="flex flex-1 flex-col gap-1 text-[13px] overflow-y-auto">
            @forelse ($navItems as $navKey => $navItem)
                @php
                    $isActive = $activeTab === $navKey;
                @endphp

                {{-- Section Label Separator --}}
                @if ($search === '' && ! empty($navItem['section_label']))
                    <div class="px-2.5 pt-3 pb-1 first:pt-0">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                            {{ $navItem['section_label'] }}
                        </span>
                    </div>
                @endif

                <button
                    type="button"
                    wire:click="setTab('{{ $navKey }}')"
                    class="group relative flex items-center gap-3 rounded-md px-3 py-2 text-left font-medium transition-all {{ $isActive ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs' : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white' }}"
                >
                    {{-- Windows 11 Active Left Accent Pill Indicator --}}
                    @if ($isActive)
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 h-4 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] }});"></span>
                    @endif

                    <div class="flex size-6 items-center justify-center rounded-md {{ $navItem['color'] }} shadow-2xs shrink-0 transition-transform group-hover:scale-105">
                        <flux:icon :name="$navItem['icon']" class="size-3.5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="truncate block {{ $isActive ? 'font-semibold text-neutral-900 dark:text-white' : '' }}">{{ $navItem['label'] }}</span>
                    </div>
                </button>
            @empty
                <div class="px-3 py-8 text-center">
                    <flux:icon name="magnifying-glass" class="mx-auto size-5 text-neutral-400 dark:text-neutral-500 mb-1.5" />
                    <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">{{ $this->t('no_results_title') }}</p>
                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $this->t('no_results_desc', ['search' => $search]) }}</p>
                    <button
                        type="button"
                        wire:click="$set('search', '')"
                        class="mt-2.5 inline-flex items-center gap-1 rounded-md bg-neutral-200/80 dark:bg-white/10 px-2.5 py-1 text-[11px] font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-300 dark:hover:bg-white/15 transition-colors"
                    >
                        {{ $this->t('clear_filter') }}
                    </button>
                </div>
            @endforelse
        </nav>

        {{-- Footer Save Status Indicator --}}
        @if ($saveStatus)
            {{-- <div class="mt-auto rounded-md bg-emerald-500/10 border border-emerald-500/20 p-2 text-xs font-medium text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                <flux:icon name="check-circle" class="size-3.5 shrink-0 text-emerald-500" />
                <span class="truncate">{{ $saveStatus }}</span>
            </div> --}}
        @endif
    </aside>

    {{-- ========================================================= --}}
    {{-- MAIN CONTENT PANEL (RIGHT - WINDOWS 11 FLUENT MICA) --}}
    {{-- ========================================================= --}}
    <main class="flex flex-1 flex-col overflow-y-auto p-6 md:p-8 bg-[#f3f3f3] dark:bg-[#1f1f1f]">
        @if ($activeTab === 'appearance')
            @include(view()->exists('pages.minios.apps.settings.appearance') ? 'pages.minios.apps.settings.appearance' : 'minios::apps.settings.appearance')
        @elseif ($activeTab === 'dock')
            @include(view()->exists('pages.minios.apps.settings.dock') ? 'pages.minios.apps.settings.dock' : 'minios::apps.settings.dock')
        @elseif ($activeTab === 'window_manager')
            @include(view()->exists('pages.minios.apps.settings.window') ? 'pages.minios.apps.settings.window' : 'minios::apps.settings.window')
        @elseif ($activeTab === 'notifications')
            @include(view()->exists('pages.minios.apps.settings.notifications') ? 'pages.minios.apps.settings.notifications' : 'minios::apps.settings.notifications')
        @elseif ($activeTab === 'locale_time')
            @include(view()->exists('pages.minios.apps.settings.locale') ? 'pages.minios.apps.settings.locale' : 'minios::apps.settings.locale')
        @elseif ($activeTab === 'account')
            @include(view()->exists('pages.minios.apps.settings.account') ? 'pages.minios.apps.settings.account' : 'minios::apps.settings.account')
        @elseif ($activeTab === 'filesystem' || $activeTab === 'services')
            @include(view()->exists('pages.minios.apps.settings.filesystem') ? 'pages.minios.apps.settings.filesystem' : 'minios::apps.settings.filesystem')
        @elseif ($activeTab === 'mail')
            @include(view()->exists('pages.minios.apps.settings.mail') ? 'pages.minios.apps.settings.mail' : 'minios::apps.settings.mail')
        @endif
    </main>
</div>
