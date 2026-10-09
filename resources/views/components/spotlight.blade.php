<div
    x-cloak
    x-show="spotlightOpen"
    @keydown.escape.window="closeSpotlight()"
    style="display: none;"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95 -translate-y-4"
    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
    x-transition:leave-end="opacity-0 scale-95 -translate-y-4"
    class="fixed inset-0 z-[9600] flex justify-center items-start pt-[12vh] sm:pt-[15vh] px-4 select-none"
>
    {{-- Dimmed Backdrop (Without backdrop blur) --}}
    <div
        class="fixed inset-0 bg-black/30 dark:bg-black/60 transition-opacity"
        @click="closeSpotlight()"
        aria-hidden="true"
    ></div>

    {{-- Spotlight Modal Box (Solid, Shadow only, No Backdrop Blur) --}}
    <div
        @click.stop
        class="
            relative
            w-full
            max-w-xl
            overflow-hidden
            rounded-2xl
            border
            border-black/10
            dark:border-white/10
            bg-white
            dark:bg-[#1e1e1e]
            shadow-2xl
            shadow-black/30
            dark:shadow-black/70
            flex
            flex-col
            text-neutral-800
            dark:text-neutral-200
        "
    >
        {{-- Search Input Bar --}}
        <div class="relative flex items-center px-4 py-3.5 border-b border-black/5 dark:border-white/10 gap-3">
            <div class="shrink-0 text-neutral-400 dark:text-neutral-500">
                <flux:icon name="magnifying-glass" class="size-5" />
            </div>

            <input
                id="minios-spotlight-input"
                x-ref="spotlightInput"
                x-model="spotlightQuery"
                type="text"
                autocomplete="off"
                spellcheck="false"
                placeholder="{{ __('Search apps or calculate math (e.g. 50 * 12 + 100)...') }}"
                class="w-full bg-transparent text-base text-neutral-900 dark:text-white placeholder:text-neutral-400 dark:placeholder:text-neutral-500 focus:outline-none"
                @keydown.arrow-down.prevent="selectNextSpotlightItem()"
                @keydown.arrow-up.prevent="selectPrevSpotlightItem()"
                @keydown.enter.prevent="executeSpotlightItem(spotlightResults[spotlightSelectedIndex])"
                @keydown.escape.prevent="closeSpotlight()"
            />

            {{-- Clear button if query has text --}}
            <button
                type="button"
                x-show="spotlightQuery.length > 0"
                @click="spotlightQuery = ''; $refs.spotlightInput?.focus()"
                class="rounded-md p-1 text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                title="{{ __('Clear') }}"
            >
                <flux:icon name="x-mark" class="size-4" />
            </button>

            {{-- ESC Badge --}}
            <button
                type="button"
                @click="closeSpotlight()"
                class="hidden sm:inline-flex items-center rounded border border-black/10 dark:border-white/10 px-1.5 py-0.5 text-[10px] font-mono text-neutral-400 dark:text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-200"
            >
                ESC
            </button>
        </div>

        {{-- Results List Container --}}
        <div class="max-h-[380px] overflow-y-auto p-2 space-y-1">
            {{-- Empty State --}}
            <div
                x-show="spotlightResults.length === 0"
                class="flex flex-col items-center justify-center py-10 text-center"
            >
                <flux:icon name="magnifying-glass" class="size-8 text-neutral-300 dark:text-neutral-600 mb-2" />
                <p class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">{{ __('No results found') }}</p>
                <p class="text-[11px] text-neutral-400 dark:text-neutral-500 mt-0.5">{{ __('Try searching with another keyword or type a math formula') }}</p>
            </div>

            {{-- Items Loop --}}
            <template x-for="(item, index) in spotlightResults" :key="item.id + '_' + index">
                <div
                    @click="executeSpotlightItem(item)"
                    @mouseenter="spotlightSelectedIndex = index"
                    :data-spotlight-item-selected="spotlightSelectedIndex === index"
                    :class="spotlightSelectedIndex === index
                        ? 'bg-indigo-600 text-white shadow-sm'
                        : 'hover:bg-black/5 dark:hover:bg-white/5 text-neutral-800 dark:text-neutral-200'"
                    class="group flex items-center justify-between rounded-xl px-3 py-2.5 transition-colors cursor-pointer"
                >
                    {{-- Left info --}}
                    <div class="flex items-center gap-3 min-w-0">
                        {{-- Icon for Calculator --}}
                        <template x-if="item.type === 'calculator'">
                            <div
                                :class="spotlightSelectedIndex === index ? 'bg-white/20 text-white' : 'bg-indigo-500/15 text-indigo-600 dark:text-indigo-400'"
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl font-bold shadow-xs"
                            >
                                <flux:icon name="calculator" class="size-5" />
                            </div>
                        </template>

                        {{-- Icon for Application --}}
                        <template x-if="item.type === 'app'">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl p-1">
                                @foreach (config('desktop.applications') as $appId => $application)
                                    <template x-if="item.id === '{{ $appId }}'">
                                        <div class="flex size-8 items-center justify-center">
                                            <x-minios.icon :name="$application['icon']" class="size-8" />
                                        </div>
                                    </template>
                                @endforeach

                                <template x-if="!@js(array_keys(config('desktop.applications'))).includes(item.id)">
                                    <div class="flex size-8 items-center justify-center">
                                        <img
                                            src="{{ asset('minios/images/logo.png') }}"
                                            :alt="item.title"
                                            class="size-8 object-contain"
                                        />
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Title & Subtitle --}}
                        <div class="min-w-0 flex-1">
                            <template x-if="item.type === 'calculator'">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-base font-bold tracking-tight" x-text="item.title"></span>
                                        <span
                                            :class="spotlightSelectedIndex === index ? 'bg-white/20 text-white' : 'bg-black/5 dark:bg-white/10 text-neutral-500 dark:text-neutral-400'"
                                            class="rounded px-1.5 py-0.2 text-[10px] font-mono uppercase"
                                        >{{ __('Math Result') }}</span>
                                    </div>
                                    <p
                                        :class="spotlightSelectedIndex === index ? 'text-white/80' : 'text-neutral-500 dark:text-neutral-400'"
                                        class="text-xs truncate font-mono mt-0.5"
                                        x-text="item.subtitle"
                                    ></p>
                                </div>
                            </template>

                            <template x-if="item.type === 'app'">
                                <div>
                                    <div class="text-sm font-semibold truncate leading-tight" x-text="item.title"></div>
                                    <p
                                        :class="spotlightSelectedIndex === index ? 'text-white/80' : 'text-neutral-500 dark:text-neutral-400'"
                                        class="text-xs truncate mt-0.5"
                                        x-text="item.subtitle"
                                    ></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Right Action Hint --}}
                    <div class="shrink-0 flex items-center gap-2 pl-2">
                        <span
                            x-show="spotlightSelectedIndex === index"
                            class="hidden sm:inline-flex items-center gap-1 text-[11px] font-medium opacity-90"
                        >
                            <span x-text="item.type === 'calculator' ? '{{ __('Copy & Open') }}' : '{{ __('Open App') }}'"></span>
                            <span class="rounded bg-white/20 px-1 py-0.5 font-mono text-[9px]">↵</span>
                        </span>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer Controls & Shortkeys --}}
        <div class="flex items-center justify-between border-t border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] px-4 py-2.5 text-[11px] text-neutral-500 dark:text-neutral-400">
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1">
                    <kbd class="rounded border border-black/10 dark:border-white/10 px-1 font-mono text-[10px]">↑</kbd>
                    <kbd class="rounded border border-black/10 dark:border-white/10 px-1 font-mono text-[10px]">↓</kbd>
                    <span>{{ __('Navigate') }}</span>
                </span>
                <span>•</span>
                <span class="flex items-center gap-1">
                    <kbd class="rounded border border-black/10 dark:border-white/10 px-1 font-mono text-[10px]">↵</kbd>
                    <span>{{ __('Open') }}</span>
                </span>
                <span>•</span>
                <span class="flex items-center gap-1">
                    <kbd class="rounded border border-black/10 dark:border-white/10 px-1 font-mono text-[10px]">esc</kbd>
                    <span>{{ __('Close') }}</span>
                </span>
            </div>

            <div class="hidden sm:flex items-center gap-1.5 font-mono text-[10px]">
                <span class="opacity-70">{{ __('Shortcut') }}:</span>
                <span class="rounded bg-black/5 dark:bg-white/10 px-1.5 py-0.5">⌘ Space</span>
                <span class="opacity-50">/</span>
                <span class="rounded bg-black/5 dark:bg-white/10 px-1.5 py-0.5">Win + S</span>
            </div>
        </div>
    </div>
</div>
