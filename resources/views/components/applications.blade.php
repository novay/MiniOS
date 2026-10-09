<div
    x-cloak
    x-show="applicationsOpen"
    x-data="{ searchQuery: '' }"
    x-init="$watch('applicationsOpen', value => { if(value) { searchQuery = ''; $nextTick(() => $refs.searchInput?.focus()); } })"
    @click.self="applicationsOpen = false"
    class="fixed inset-0 z-[9500] flex flex-col overflow-y-auto overflow-x-hidden bg-[#1d171b]/90 backdrop-blur-3xl transition-all duration-200 select-none"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-[1.02]"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-[1.02]"
>
    {{-- Close / Escape Button --}}
    <button
        type="button"
        @click="applicationsOpen = false"
        title="{{ __('Close (Esc)') }}"
        class="absolute top-5 right-6 z-10 flex size-9 items-center justify-center rounded-full bg-white/10 text-white/70 backdrop-blur-md transition-colors hover:bg-white/20 hover:text-white"
    >
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 6L6 18M6 6l12 12" />
        </svg>
    </button>

    <div
        class="flex min-h-full flex-col items-center justify-between py-12 px-6"
        @click.self="applicationsOpen = false"
    >
        {{-- Search Input --}}
        <div class="w-full max-w-xl px-6">
            <div class="flex items-center gap-3 rounded-full border border-white/10 bg-black/40 px-5 py-3 shadow-2xl backdrop-blur-md transition-all focus-within:border-white/30 focus-within:bg-black/50">
                <svg
                    class="size-5 text-white/50"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <circle cx="11" cy="11" r="7" />
                    <path d="M20 20L16.5 16.5" />
                </svg>

                <input
                    x-ref="searchInput"
                    x-model="searchQuery"
                    type="text"
                    placeholder="{{ __('Type to search applications...') }}"
                    class="w-full border-0 bg-transparent text-sm text-white outline-none placeholder:text-white/40"
                    @keydown.escape.stop="if (searchQuery) searchQuery = ''; else applicationsOpen = false;"
                >

                <button
                    type="button"
                    x-show="searchQuery.length > 0"
                    @click="searchQuery = ''; $refs.searchInput?.focus()"
                    class="text-white/40 transition-colors hover:text-white"
                >
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        @php
            $sortedApplications = collect(config('desktop.applications'))
                ->except('about')
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
        @endphp

        {{-- Applications Grid --}}
        <div
            @click.self="applicationsOpen = false"
            class="my-auto grid grid-cols-3 gap-x-10 gap-y-8 py-10 sm:grid-cols-4 lg:grid-cols-6"
        >
            @foreach ($sortedApplications as $id => $application)
                <button
                    type="button"
                    x-show="!searchQuery || @js(strtolower($application['name'])).includes(searchQuery.toLowerCase().trim()) || @js(strtolower($id)).includes(searchQuery.toLowerCase().trim())"
                    @click="openApplication(@js($id), { fromLauncher: true })"
                    @contextmenu.prevent.stop="openLauncherContextMenu($event, @js($id))"
                    class="group flex w-24 flex-col items-center rounded-xl p-2 transition hover:bg-white/10"
                >
                    <div class="flex size-20 items-center justify-center transition-transform duration-150 group-hover:scale-110">
                        <x-minios.icon
                            :name="$application['icon']"
                            class="size-16"
                        />
                    </div>

                    <span class="text-sm font-medium text-white/90 text-center line-clamp-1">
                        {{ $application['name'] }}
                    </span>
                </button>
            @endforeach
        </div>

        {{-- Page Indicator --}}
        <div
            @click.self="applicationsOpen = false"
            class="mt-auto flex items-center gap-2 pt-4"
        >
            <div class="size-2 rounded-full bg-white"></div>
            <div class="size-2 rounded-full bg-white/30"></div>
        </div>
    </div>
</div>
