<div class="flex h-full min-h-125 w-full overflow-hidden bg-[#f3f3f3] dark:bg-[#202020] text-neutral-800 dark:text-neutral-100 font-sans select-none relative">

    {{-- ========================================================= --}}
    {{-- WINDOWS 11 FLUENT NAVIGATION RAIL (LEFT SIDEBAR)          --}}
    {{-- ========================================================= --}}
    <aside class="flex w-52 sm:w-60 shrink-0 flex-col border-r border-neutral-200/90 dark:border-white/5 bg-[#f8f8f8]/85 dark:bg-[#202020]/90 p-3.5 backdrop-blur-xl gap-4 text-xs select-none">
        {{-- App Header Title --}}
        <div class="flex items-center gap-2.5 px-2 py-1">
            <div class="flex size-7 items-center justify-center rounded-lg text-white shadow-2xs shrink-0" style="background-color: var(--accent-color, {{ $accent['hex'] }});">
                <flux:icon name="chart-bar" class="size-4" />
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-xs font-bold tracking-tight text-neutral-900 dark:text-white truncate">{{ $this->t('app_title') }}</h2>
                <p class="text-[10px] text-neutral-500 dark:text-neutral-400 truncate">{{ $this->t('app_subtitle') }}</p>
            </div>
        </div>

        {{-- Navigation Menu Items --}}
        <nav class="flex flex-1 flex-col gap-1">
            {{-- Tab 1: Proses --}}
            @php $isProc = $activeTab === 'processes'; @endphp
            <button
                type="button"
                wire:click="setTab('processes')"
                class="group relative flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-left transition-all {{ $isProc ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white' }}"
            >
                @if ($isProc)
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 h-4 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] }});"></span>
                @endif
                <div class="flex size-5 items-center justify-center rounded {{ $isProc ? $accent['text'] : 'text-neutral-500 dark:text-neutral-400' }}">
                    <flux:icon name="squares-2x2" class="size-4" />
                </div>
                <div class="min-w-0 flex-1">
                    <span class="block truncate">{{ $this->t('tab_processes') }}</span>
                </div>
            </button>

            {{-- Tab 2: Performa --}}
            @php $isPerf = $activeTab === 'performance' || $activeTab === 'cpu' || $activeTab === 'memory'; @endphp
            <button
                type="button"
                wire:click="setTab('performance')"
                class="group relative flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-left transition-all {{ $isPerf ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white' }}"
            >
                @if ($isPerf)
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 h-4 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] }});"></span>
                @endif
                <div class="flex size-5 items-center justify-center rounded {{ $isPerf ? $accent['text'] : 'text-neutral-500 dark:text-neutral-400' }}">
                    <flux:icon name="presentation-chart-line" class="size-4" />
                </div>
                <div class="min-w-0 flex-1">
                    <span class="block truncate">{{ $this->t('tab_performance') }}</span>
                </div>
            </button>

            {{-- Tab 3: Penyimpanan --}}
            @php $isDisk = $activeTab === 'disk'; @endphp
            <button
                type="button"
                wire:click="setTab('disk')"
                class="group relative flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-left transition-all {{ $isDisk ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white' }}"
            >
                @if ($isDisk)
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 h-4 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] }});"></span>
                @endif
                <div class="flex size-5 items-center justify-center rounded {{ $isDisk ? $accent['text'] : 'text-neutral-500 dark:text-neutral-400' }}">
                    <flux:icon name="server" class="size-4" />
                </div>
                <div class="min-w-0 flex-1">
                    <span class="block truncate">{{ $this->t('tab_disk') }}</span>
                </div>
            </button>

            {{-- Tab 4: Detail Sistem --}}
            @php $isSys = $activeTab === 'system'; @endphp
            <button
                type="button"
                wire:click="setTab('system')"
                class="group relative flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-left transition-all {{ $isSys ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white' }}"
            >
                @if ($isSys)
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 h-4 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] }});"></span>
                @endif
                <div class="flex size-5 items-center justify-center rounded {{ $isSys ? $accent['text'] : 'text-neutral-500 dark:text-neutral-400' }}">
                    <flux:icon name="information-circle" class="size-4" />
                </div>
                <div class="min-w-0 flex-1">
                    <span class="block truncate">{{ $this->t('tab_system') }}</span>
                </div>
            </button>
        </nav>

        {{-- Bottom Telemetry Widget --}}
        <div class="mt-auto rounded-xl bg-white/70 dark:bg-white/5 border border-neutral-200/90 dark:border-white/5 p-3 space-y-2 shadow-2xs">
            <div class="flex items-center justify-between text-[11px]">
                <span class="text-neutral-500 dark:text-neutral-400 font-medium">{{ $this->t('system_status') }}</span>
                <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold text-[10px]">
                    <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ $this->t('normal') }}
                </span>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-[10px] text-neutral-500">
                    <span>{{ $this->t('memory') }}</span>
                    <span>{{ $this->systemStats['memory_usage'] }}</span>
                </div>
                <div class="w-full bg-neutral-200/80 dark:bg-white/10 h-1 rounded-full overflow-hidden">
                    <div class="h-full rounded-full" style="width: 28%; background-color: var(--accent-color, {{ $accent['hex'] }});"></div>
                </div>
            </div>
        </div>
    </aside>

    {{-- ========================================================= --}}
    {{-- MAIN VIEWPORT & WINDOWS 11 HEADER                         --}}
    {{-- ========================================================= --}}
    <div class="flex flex-1 flex-col overflow-hidden">
        {{-- Windows 11 Command Header Bar --}}
        <header class="flex shrink-0 items-center justify-between border-b border-neutral-200/90 dark:border-white/5 bg-white/70 dark:bg-[#2b2b2b]/70 px-6 py-3 backdrop-blur-xl gap-3">
            <div>
                {{-- Windows 11 Breadcrumb --}}
                <div class="flex items-center gap-1.5 text-[11px] font-medium text-neutral-500 dark:text-neutral-400">
                    <span>{{ $this->t('app_title') }}</span>
                    <flux:icon name="chevron-right" class="size-2.5 text-neutral-400" />
                    <span class="text-neutral-700 dark:text-neutral-300 capitalize">
                        @if ($activeTab === 'processes') {{ $this->t('crumb_processes') }}
                        @elseif ($activeTab === 'performance' || $activeTab === 'cpu' || $activeTab === 'memory') {{ $this->t('crumb_performance') }}
                        @elseif ($activeTab === 'disk') {{ $this->t('crumb_disk') }}
                        @else {{ $this->t('crumb_system') }}
                        @endif
                    </span>
                </div>
                <h1 class="text-base font-bold tracking-tight text-neutral-900 dark:text-white mt-0.5">
                    @if ($activeTab === 'processes') {{ $this->t('header_processes') }}
                    @elseif ($activeTab === 'performance' || $activeTab === 'cpu' || $activeTab === 'memory') {{ $this->t('header_performance') }}
                    @elseif ($activeTab === 'disk') {{ $this->t('header_disk') }}
                    @else {{ $this->t('header_system') }}
                    @endif
                </h1>
            </div>

            {{-- Action Controls --}}
            <div class="flex items-center gap-2">
                @if ($activeTab === 'processes')
                    {{-- Search Filter for Processes --}}
                    <div class="relative w-44 sm:w-56">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                            <flux:icon name="magnifying-glass" class="size-3.5 text-neutral-400" />
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.150ms="searchProcess"
                            placeholder="{{ $this->t('search_processes_placeholder') }}"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] py-1 pl-8 pr-7 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 shadow-2xs focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
                        />
                        @if ($searchProcess !== '')
                            <button
                                type="button"
                                wire:click="$set('searchProcess', '')"
                                class="absolute inset-y-0 right-0 flex items-center pr-2 text-neutral-400 hover:text-neutral-700 dark:hover:text-white"
                            >
                                <flux:icon name="x-mark" class="size-3.5" />
                            </button>
                        @endif
                    </div>

                    {{-- End Task Button --}}
                    @if ($selectedPid)
                        <button
                            type="button"
                            wire:click="endProcess"
                            class="flex items-center gap-1.5 rounded-md bg-rose-600 hover:bg-rose-700 px-3 py-1 text-xs font-medium text-white shadow-2xs active:scale-98 transition-all"
                        >
                            <flux:icon name="x-circle" class="size-3.5" />
                            <span>{{ $this->t('end_task') }}</span>
                        </button>
                    @endif
                @endif

                {{-- Refresh Button --}}
                <button
                    type="button"
                    wire:click="$refresh"
                    title="{{ $this->t('refresh') }}"
                    class="flex size-7 items-center justify-center rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-[#333] shadow-2xs transition-all"
                >
                    <flux:icon name="arrow-path" class="size-3.5" />
                </button>
            </div>
        </header>

        {{-- Main Content Viewer --}}
        <main class="flex-1 overflow-y-auto p-5 sm:p-6 bg-white dark:bg-[#191919]">
            {{-- Shell Execution Restriction Notice (if server does not support commands) --}}
            @if (! $this->systemStats['is_shell_supported'])
                <div class="mb-4 flex items-start gap-3 rounded-xl border border-amber-500/30 bg-amber-500/10 p-3.5 text-xs text-amber-800 dark:text-amber-200 shadow-2xs">
                    <flux:icon name="exclamation-triangle" class="size-5 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5" />
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h4 class="font-semibold text-amber-900 dark:text-amber-100">{{ $this->t('safe_mode_title') }}</h4>
                            <span class="rounded bg-amber-500/20 px-1.5 py-0.5 text-[10px] font-medium text-amber-800 dark:text-amber-200">{{ $this->t('safe_mode_badge') }}</span>
                        </div>
                        <p class="mt-0.5 text-[11px] text-amber-700 dark:text-amber-300 leading-relaxed">
                            {{ $this->t('safe_mode_desc') }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- Action Feedback Banner --}}
            @if ($feedbackMessage)
                <div class="mb-4 flex items-center justify-between rounded-xl border p-3 text-xs shadow-2xs transition-all {{ $feedbackType === 'success' ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200' : ($feedbackType === 'error' ? 'border-rose-500/30 bg-rose-500/10 text-rose-800 dark:text-rose-200' : 'border-blue-500/30 bg-blue-500/10 text-blue-800 dark:text-blue-200') }}">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <flux:icon name="{{ $feedbackType === 'success' ? 'check-circle' : ($feedbackType === 'error' ? 'x-circle' : 'information-circle') }}" class="size-4 shrink-0" />
                        <span class="font-medium truncate">{{ $feedbackMessage }}</span>
                    </div>
                    <button type="button" wire:click="$set('feedbackMessage', null)" class="text-neutral-400 hover:text-neutral-700 dark:hover:text-white shrink-0 ml-2">
                        <flux:icon name="x-mark" class="size-3.5" />
                    </button>
                </div>
            @endif

            @if ($activeTab === 'processes')
                {{-- ========================================================= --}}
                {{-- TAB 1: PROSES (WINDOWS 11 PROCESSES TABLE)                --}}
                {{-- ========================================================= --}}
                {{-- Filter Toolbar --}}
                <div class="mb-3 flex items-center justify-between flex-wrap gap-2 text-xs">
                    <div class="flex rounded-lg bg-neutral-100 dark:bg-white/5 p-0.5 text-[11px] border border-neutral-200/60 dark:border-white/5">
                        <button
                            type="button"
                            wire:click="setProcessFilter('all')"
                            class="px-2.5 py-1 rounded-md transition-all {{ $processFilter === 'all' ? 'bg-white dark:bg-[#333] text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white' }}"
                        >
                            {{ $this->t('filter_all') }}
                        </button>
                        <button
                            type="button"
                            wire:click="setProcessFilter('system')"
                            class="px-2.5 py-1 rounded-md transition-all {{ $processFilter === 'system' ? 'bg-white dark:bg-[#333] text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white' }}"
                        >
                            {{ $this->t('filter_system') }}
                        </button>
                        <button
                            type="button"
                            wire:click="setProcessFilter('minios')"
                            class="px-2.5 py-1 rounded-md transition-all {{ $processFilter === 'minios' ? 'bg-white dark:bg-[#333] text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white' }}"
                        >
                            {{ $this->t('filter_minios') }}
                        </button>
                    </div>

                    <div class="flex items-center gap-2 text-[11px] text-neutral-500 dark:text-neutral-400">
                        @if ($this->systemStats['is_shell_supported'])
                            <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium">
                                <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Live Host Process
                            </span>
                            <span>•</span>
                        @endif
                        <span>Total <strong>{{ count($this->processes) }}</strong> proses</span>
                    </div>
                </div>

                <div class="rounded-xl border border-neutral-200/90 dark:border-white/10 bg-white dark:bg-[#202020] overflow-hidden shadow-2xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-neutral-200/90 dark:border-white/10 text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 bg-neutral-50/70 dark:bg-white/5 select-none">
                                    <th class="py-2.5 px-4">{{ $this->t('th_process_name') }}</th>
                                    <th class="py-2.5 px-3">{{ $this->t('th_type') }}</th>
                                    <th class="py-2.5 px-3">{{ $this->t('th_status') }}</th>
                                    <th class="py-2.5 px-3 text-right">{{ $this->t('th_cpu') }}</th>
                                    <th class="py-2.5 px-3 text-right">{{ $this->t('th_memory') }}</th>
                                    <th class="py-2.5 px-3 text-right">{{ $this->t('th_pid') }}</th>
                                    <th class="py-2.5 px-3 text-right">{{ $this->t('th_user') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-white/5">
                                @forelse ($this->processes as $proc)
                                    @php
                                        $isSelected = $selectedPid === $proc['pid'];
                                        $cpuVal = $proc['cpu_val'] ?? (float) str_replace('%', '', $proc['cpu']);
                                        $isMiniosProc = ($proc['type'] ?? '') === 'minios';
                                    @endphp
                                    <tr
                                        wire:click="selectProcess({{ $proc['pid'] }})"
                                        class="cursor-pointer transition-colors {{ $isSelected ? 'bg-neutral-100 dark:bg-white/10 font-medium' : 'hover:bg-neutral-50 dark:hover:bg-white/5' }}"
                                    >
                                        {{-- Process Name & Command Snippet --}}
                                        <td class="py-2.5 px-4 flex items-center gap-2.5">
                                            <div class="flex size-7 items-center justify-center rounded-lg bg-neutral-100 dark:bg-white/5 border border-neutral-200/70 dark:border-white/10 text-neutral-600 dark:text-neutral-300 shrink-0">
                                                <flux:icon name="{{ $isMiniosProc ? 'squares-2x2' : 'cpu-chip' }}" class="size-3.5" />
                                            </div>
                                            <div class="min-w-0 max-w-xs sm:max-w-md">
                                                <span class="font-medium text-neutral-900 dark:text-white truncate block">{{ $proc['name'] }}</span>
                                                @if (! empty($proc['command']) && $proc['command'] !== $proc['name'])
                                                    <span class="text-[10px] text-neutral-400 dark:text-neutral-500 truncate block" title="{{ $proc['command'] }}">{{ $proc['command'] }}</span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Type Badge --}}
                                        <td class="py-2.5 px-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $isMiniosProc ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'bg-neutral-200/70 dark:bg-white/10 text-neutral-700 dark:text-neutral-300' }}">
                                                {{ $isMiniosProc ? 'MiniOS' : 'Host OS' }}
                                            </span>
                                        </td>

                                        {{-- Status --}}
                                        <td class="py-2.5 px-3 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium {{ $proc['status'] === 'Running' || $proc['status'] === 'Active' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-neutral-200/70 dark:bg-white/10 text-neutral-600 dark:text-neutral-400' }}">
                                                <span class="size-1.5 rounded-full {{ $proc['status'] === 'Running' || $proc['status'] === 'Active' ? 'bg-emerald-500' : 'bg-neutral-400' }}"></span>
                                                {{ $proc['status'] }}
                                            </span>
                                        </td>

                                        {{-- CPU % Heat Map --}}
                                        <td class="py-2.5 px-3 text-right font-medium whitespace-nowrap {{ $cpuVal > 1.0 ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-neutral-700 dark:text-neutral-300' }}">
                                            <span class="inline-block rounded px-1.5 py-0.5 {{ $cpuVal > 1.0 ? 'bg-amber-500/10' : '' }}">
                                                {{ $proc['cpu'] }}
                                            </span>
                                        </td>

                                        {{-- Memory --}}
                                        <td class="py-2.5 px-3 text-right text-neutral-700 dark:text-neutral-300 whitespace-nowrap">
                                            {{ $proc['memory'] }}
                                        </td>

                                        {{-- PID --}}
                                        <td class="py-2.5 px-3 text-right text-neutral-400 whitespace-nowrap">
                                            {{ $proc['pid'] }}
                                        </td>

                                        {{-- User --}}
                                        <td class="py-2.5 px-3 text-right text-neutral-500 whitespace-nowrap">
                                            {{ $proc['user'] }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-neutral-400">
                                            <flux:icon name="magnifying-glass" class="mx-auto size-6 text-neutral-400 mb-1" />
                                            <span>Tidak ada proses yang cocok dengan kata kunci.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            @elseif ($activeTab === 'performance' || $activeTab === 'cpu' || $activeTab === 'memory')
                {{-- ========================================================= --}}
                {{-- TAB 2: PERFORMA (LIVE FLUENT TELEMETRY GRAPHS)            --}}
                {{-- ========================================================= --}}
                <div class="space-y-5">
                    {{-- Telemetry Metric Overview Cards --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {{-- CPU Card --}}
                        <div class="rounded-xl border border-neutral-200/90 dark:border-white/10 bg-white dark:bg-[#202020] p-4 shadow-2xs space-y-2">
                            <div class="flex items-center justify-between text-xs font-semibold text-neutral-500 dark:text-neutral-400">
                                <span class="truncate">{{ $this->systemStats['cpu_model'] }}</span>
                                <span class="text-[11px] font-normal shrink-0">{{ $this->systemStats['cpu_cores'] }} Cores</span>
                            </div>
                            <div class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white" style="color: var(--accent-color, {{ $accent['hex'] }});">
                                {{ $this->systemStats['cpu_percent'] }}%
                            </div>
                            <div class="w-full bg-neutral-200/80 dark:bg-white/10 h-1.5 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" style="width: {{ min(100, max(5, $this->systemStats['cpu_percent'] * 4)) }}%; background-color: var(--accent-color, {{ $accent['hex'] }});"></div>
                            </div>
                            <div class="text-[10px] text-neutral-500 flex justify-between pt-1">
                                <span class="truncate">Load: {{ $this->systemStats['cpu_load_str'] }}</span>
                            </div>
                        </div>

                        {{-- Memory Card --}}
                        <div class="rounded-xl border border-neutral-200/90 dark:border-white/10 bg-white dark:bg-[#202020] p-4 shadow-2xs space-y-2">
                            <div class="flex items-center justify-between text-xs font-semibold text-neutral-500 dark:text-neutral-400">
                                <span>Memori RAM</span>
                                <span class="text-[11px] font-normal">Fisik: {{ $this->systemStats['total_ram'] }}</span>
                            </div>
                            <div class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">
                                {{ $this->systemStats['memory_usage'] }}
                            </div>
                            <div class="w-full bg-neutral-200/80 dark:bg-white/10 h-1.5 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-emerald-500" style="width: 25%;"></div>
                            </div>
                            <div class="text-[11px] text-neutral-500 flex justify-between pt-1">
                                <span>Puncak: {{ $this->systemStats['memory_peak'] }}</span>
                                <span>Batas PHP: {{ $this->systemStats['memory_limit'] }}</span>
                            </div>
                        </div>

                        {{-- Storage Card --}}
                        <div class="rounded-xl border border-neutral-200/90 dark:border-white/10 bg-white dark:bg-[#202020] p-4 shadow-2xs space-y-2">
                            <div class="flex items-center justify-between text-xs font-semibold text-neutral-500 dark:text-neutral-400">
                                <span>Penyimpanan Disk</span>
                                <span class="text-[11px] font-normal">{{ $this->systemStats['storage_free'] }} Bebas</span>
                            </div>
                            <div class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">
                                {{ $this->systemStats['storage_percent'] }}%
                            </div>
                            <div class="w-full bg-neutral-200/80 dark:bg-white/10 h-1.5 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-amber-500" style="width: {{ $this->systemStats['storage_percent'] }}%;"></div>
                            </div>
                            <div class="text-[11px] text-neutral-500 flex justify-between pt-1">
                                <span>Terpakai: {{ $this->systemStats['storage_used'] }}</span>
                                <span>Total: {{ $this->systemStats['storage_total'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Windows 11 Live Telemetry Grid Waveform --}}
                    <div class="rounded-xl border border-neutral-200/90 dark:border-white/10 bg-white dark:bg-[#202020] p-5 shadow-2xs space-y-3">
                        <div class="flex items-center justify-between text-xs font-semibold text-neutral-900 dark:text-white">
                            <div class="flex items-center gap-2">
                                <flux:icon name="presentation-chart-line" class="size-4 text-neutral-500" />
                                <span>Riwayat Beban CPU &amp; Aktivitas Sistem (60 Detik)</span>
                            </div>
                            <span class="text-neutral-500 font-normal">100% Utilisasi</span>
                        </div>

                        {{-- SVG Live Telemetry Graph with Grid Lines --}}
                        <div class="relative h-44 w-full rounded-lg bg-neutral-50 dark:bg-[#161616] border border-neutral-200/70 dark:border-white/5 overflow-hidden p-2">
                            {{-- Windows 11 Grid Lines --}}
                            <div class="absolute inset-0 grid grid-rows-4 grid-cols-6 pointer-events-none stroke-neutral-200 dark:stroke-white/5 opacity-40">
                                <div class="border-b border-r border-dashed border-neutral-300 dark:border-white/10"></div>
                                <div class="border-b border-r border-dashed border-neutral-300 dark:border-white/10"></div>
                                <div class="border-b border-r border-dashed border-neutral-300 dark:border-white/10"></div>
                                <div class="border-b border-r border-dashed border-neutral-300 dark:border-white/10"></div>
                                <div class="border-b border-r border-dashed border-neutral-300 dark:border-white/10"></div>
                                <div class="border-b border-neutral-300 dark:border-white/10"></div>
                                <div class="border-b border-r border-dashed border-neutral-300 dark:border-white/10"></div>
                                <div class="border-b border-r border-dashed border-neutral-300 dark:border-white/10"></div>
                                <div class="border-b border-r border-dashed border-neutral-300 dark:border-white/10"></div>
                                <div class="border-b border-r border-dashed border-neutral-300 dark:border-white/10"></div>
                                <div class="border-b border-r border-dashed border-neutral-300 dark:border-white/10"></div>
                                <div class="border-b border-neutral-300 dark:border-white/10"></div>
                            </div>

                            {{-- Dynamic SVG Graph --}}
                            <svg class="h-full w-full overflow-visible" viewBox="0 0 500 150" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="cpu_wave_grad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="var(--accent-color, {{ $accent['hex'] }})" stop-opacity="0.35" />
                                        <stop offset="100%" stop-color="var(--accent-color, {{ $accent['hex'] }})" stop-opacity="0.0" />
                                    </linearGradient>
                                </defs>
                                {{-- Area Fill --}}
                                <path
                                    d="{{ $this->cpuWavePath['area'] }}"
                                    fill="url(#cpu_wave_grad)"
                                    class="transition-all duration-700 ease-in-out"
                                />
                                {{-- Line Stroke --}}
                                <path
                                    d="{{ $this->cpuWavePath['line'] }}"
                                    fill="none"
                                    stroke="var(--accent-color, {{ $accent['hex'] }})"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    class="transition-all duration-700 ease-in-out"
                                />
                            </svg>
                        </div>

                        {{-- Details Footer Grid --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-2 text-xs">
                            <div>
                                <span class="text-neutral-500 block">Utilisasi Terakhir</span>
                                <span class="font-bold text-neutral-900 dark:text-white mt-0.5 block">{{ $this->systemStats['cpu_percent'] }}%</span>
                            </div>
                            <div>
                                <span class="text-neutral-500 block">Status Database</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400 mt-0.5 block">{{ $this->systemStats['db_status'] }}</span>
                            </div>
                            <div>
                                <span class="text-neutral-500 block">OPcache Accelerator</span>
                                <span class="font-bold text-neutral-900 dark:text-white mt-0.5 block">{{ $this->systemStats['opcache_enabled'] ? 'Aktif' : 'Nonaktif' }}</span>
                            </div>
                            <div>
                                <span class="text-neutral-500 block">Waktu Operasional</span>
                                <span class="font-bold text-neutral-900 dark:text-white mt-0.5 block">{{ $this->systemStats['uptime'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            @elseif ($activeTab === 'disk')
                {{-- ========================================================= --}}
                {{-- TAB 3: PENYIMPANAN DISK (WINDOWS 11 STORAGE METRICS)      --}}
                {{-- ========================================================= --}}
                <div class="space-y-4">
                    {{-- Total Disk Summary Card --}}
                    <div class="rounded-xl border border-neutral-200/90 dark:border-white/10 bg-white dark:bg-[#202020] p-5 shadow-2xs space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500">
                                    <flux:icon name="server" class="size-5" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Partisi Utama (storage/)</h3>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Direktori penyimpanan berkas aplikasi MiniOS</p>
                                </div>
                            </div>
                            <span class="text-xl font-bold text-amber-500">{{ $this->systemStats['storage_percent'] }}% Terpakai</span>
                        </div>

                        <div class="w-full bg-neutral-200/80 dark:bg-white/10 h-2.5 rounded-full overflow-hidden">
                            <div class="h-full rounded-full" style="width: {{ $this->systemStats['storage_percent'] }}%; background-color: var(--accent-color, {{ $accent['hex'] }});"></div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 pt-2 text-center text-xs">
                            <div class="p-3 rounded-lg bg-neutral-50 dark:bg-white/5 border border-neutral-200/70 dark:border-white/5">
                                <span class="text-neutral-500 block">Total Ruang</span>
                                <span class="font-bold text-neutral-900 dark:text-white mt-0.5 block">{{ $this->systemStats['storage_total'] }}</span>
                            </div>
                            <div class="p-3 rounded-lg bg-neutral-50 dark:bg-white/5 border border-neutral-200/70 dark:border-white/5">
                                <span class="text-neutral-500 block">Ruang Terpakai</span>
                                <span class="font-bold text-amber-500 mt-0.5 block">{{ $this->systemStats['storage_used'] }}</span>
                            </div>
                            <div class="p-3 rounded-lg bg-neutral-50 dark:bg-white/5 border border-neutral-200/70 dark:border-white/5">
                                <span class="text-neutral-500 block">Ruang Bebas</span>
                                <span class="font-bold text-emerald-500 mt-0.5 block">{{ $this->systemStats['storage_free'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Storage Breakdown by Subdirectories --}}
                    <div class="rounded-xl border border-neutral-200/90 dark:border-white/10 bg-white dark:bg-[#202020] p-5 shadow-2xs space-y-3">
                        <h4 class="text-xs font-semibold text-neutral-900 dark:text-white uppercase tracking-wider text-neutral-500">Alokasi Direktori</h4>
                        <div class="divide-y divide-neutral-100 dark:divide-white/5 text-xs">
                            @foreach ($this->directorySizes as $dir)
                                <div class="py-2.5 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <flux:icon name="{{ $dir['icon'] }}" class="size-4 {{ $dir['color'] }} shrink-0" />
                                        <div class="min-w-0">
                                            <span class="font-medium text-neutral-900 dark:text-white block truncate">{{ $dir['name'] }}</span>
                                            <span class="text-[11px] text-neutral-400 block truncate">{{ $dir['desc'] }}</span>
                                        </div>
                                    </div>
                                    <span class="font-semibold text-neutral-700 dark:text-neutral-300 shrink-0 ml-4 font-mono">{{ $dir['formatted'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            @elseif ($activeTab === 'system')
                {{-- ========================================================= --}}
                {{-- TAB 4: DETAIL SISTEM (WINDOWS 11 SYSTEM ENVIRONMENT)     --}}
                {{-- ========================================================= --}}
                <div class="space-y-4">
                    {{-- Top Row: PHP & Laravel Cards --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- PHP Info --}}
                        <div class="rounded-xl border border-neutral-200/90 dark:border-white/10 bg-white dark:bg-[#202020] p-5 shadow-2xs space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-500">
                                    <flux:icon name="code-bracket" class="size-5" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">PHP Runtime</h3>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">PHP {{ $this->systemStats['php_version'] }}</p>
                                </div>
                            </div>
                            <div class="space-y-1.5 text-xs text-neutral-600 dark:text-neutral-300 pt-1">
                                <div class="flex justify-between">
                                    <span class="text-neutral-500">Batas Memori:</span>
                                    <span class="font-medium">{{ $this->systemStats['memory_limit'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-neutral-500">OPcache:</span>
                                    <span class="font-medium {{ $this->systemStats['opcache_enabled'] ? 'text-emerald-500' : 'text-neutral-400' }}">
                                        {{ $this->systemStats['opcache_enabled'] ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-neutral-500">SAPI Server:</span>
                                    <span class="font-medium">{{ php_sapi_name() }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Laravel Core Info --}}
                        <div class="rounded-xl border border-neutral-200/90 dark:border-white/10 bg-white dark:bg-[#202020] p-5 shadow-2xs space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-xl bg-rose-500/10 text-rose-500">
                                    <flux:icon name="command-line" class="size-5" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Laravel Framework</h3>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Laravel v{{ $this->systemStats['laravel_version'] }}</p>
                                </div>
                            </div>
                            <div class="space-y-1.5 text-xs text-neutral-600 dark:text-neutral-300 pt-1">
                                <div class="flex justify-between">
                                    <span class="text-neutral-500">Lingkungan:</span>
                                    <span class="font-medium capitalize">{{ app()->environment() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-neutral-500">Status Database:</span>
                                    <span class="font-medium text-emerald-500">{{ $this->systemStats['db_status'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-neutral-500">OS Host:</span>
                                    <span class="font-medium">{{ $this->systemStats['server_os'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Middle Row: Environment Details --}}
                    <div class="rounded-xl border border-neutral-200/90 dark:border-white/10 bg-white dark:bg-[#202020] p-5 shadow-2xs space-y-3.5">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-3">
                                <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500">
                                    <flux:icon name="adjustments-horizontal" class="size-4.5" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Konfigurasi Lingkungan (Environment)</h3>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Variabel konfigurasi runtime aplikasi MiniOS</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-neutral-100 dark:bg-white/10 text-neutral-700 dark:text-neutral-300">
                                <span class="size-2 rounded-full {{ app()->environment('production') ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                Mode: {{ ucfirst($this->environmentStats['app_env']) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-1 text-xs">
                            <div class="p-2.5 rounded-lg bg-neutral-50 dark:bg-white/5 border border-neutral-200/60 dark:border-white/5 space-y-1">
                                <span class="text-[11px] text-neutral-500 block">Mode Debug (APP_DEBUG)</span>
                                <span class="font-semibold {{ config('app.debug') ? 'text-amber-600 dark:text-amber-400' : 'text-neutral-700 dark:text-neutral-300' }} block">
                                    {{ $this->environmentStats['app_debug'] }}
                                </span>
                            </div>

                            <div class="p-2.5 rounded-lg bg-neutral-50 dark:bg-white/5 border border-neutral-200/60 dark:border-white/5 space-y-1">
                                <span class="text-[11px] text-neutral-500 block">URL Aplikasi (APP_URL)</span>
                                <span class="font-medium text-neutral-800 dark:text-neutral-200 block truncate">
                                    {{ $this->environmentStats['app_url'] }}
                                </span>
                            </div>

                            <div class="p-2.5 rounded-lg bg-neutral-50 dark:bg-white/5 border border-neutral-200/60 dark:border-white/5 space-y-1">
                                <span class="text-[11px] text-neutral-500 block">Zona Waktu (APP_TIMEZONE)</span>
                                <span class="font-medium text-neutral-800 dark:text-neutral-200 block">
                                    {{ $this->environmentStats['timezone'] }}
                                </span>
                            </div>

                            <div class="p-2.5 rounded-lg bg-neutral-50 dark:bg-white/5 border border-neutral-200/60 dark:border-white/5 space-y-1">
                                <span class="text-[11px] text-neutral-500 block">Driver Cache (CACHE_STORE)</span>
                                <span class="font-medium text-neutral-800 dark:text-neutral-200 block">
                                    {{ $this->environmentStats['cache_driver'] }}
                                </span>
                            </div>

                            <div class="p-2.5 rounded-lg bg-neutral-50 dark:bg-white/5 border border-neutral-200/60 dark:border-white/5 space-y-1">
                                <span class="text-[11px] text-neutral-500 block">Driver Sesi (SESSION_DRIVER)</span>
                                <span class="font-medium text-neutral-800 dark:text-neutral-200 block">
                                    {{ $this->environmentStats['session_driver'] }}
                                </span>
                            </div>

                            <div class="p-2.5 rounded-lg bg-neutral-50 dark:bg-white/5 border border-neutral-200/60 dark:border-white/5 space-y-1">
                                <span class="text-[11px] text-neutral-500 block">Driver Antrean (QUEUE_CONNECTION)</span>
                                <span class="font-medium text-neutral-800 dark:text-neutral-200 block">
                                    {{ $this->environmentStats['queue_connection'] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Bottom Section: Dependencies / Packages --}}
                    <div class="rounded-xl border border-neutral-200/90 dark:border-white/10 bg-white dark:bg-[#202020] p-5 shadow-2xs space-y-4">
                        <div class="flex items-center justify-between flex-wrap gap-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-9 items-center justify-center rounded-xl bg-sky-500/10 text-sky-500">
                                    <flux:icon name="cube" class="size-4.5" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Dependensi &amp; Paket (Packages)</h3>
                                        <span class="rounded-full bg-neutral-200/80 dark:bg-white/10 px-2 py-0.5 text-[10px] font-semibold text-neutral-700 dark:text-neutral-300">
                                            {{ $this->packageCounts['total'] }} paket
                                        </span>
                                    </div>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Pustaka Composer yang terinstal di direktori kerja</p>
                                </div>
                            </div>

                            {{-- Filters and Search Bar --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                {{-- Filter Pills --}}
                                <div class="flex rounded-lg bg-neutral-100 dark:bg-white/5 p-0.5 text-[11px] border border-neutral-200/60 dark:border-white/5">
                                    <button
                                        type="button"
                                        wire:click="setPackageFilter('all')"
                                        class="px-2.5 py-1 rounded-md transition-all {{ $packageTypeFilter === 'all' ? 'bg-white dark:bg-[#333] text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white' }}"
                                    >
                                        Semua ({{ $this->packageCounts['total'] }})
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="setPackageFilter('prod')"
                                        class="px-2.5 py-1 rounded-md transition-all {{ $packageTypeFilter === 'prod' ? 'bg-white dark:bg-[#333] text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white' }}"
                                    >
                                        Prod ({{ $this->packageCounts['prod'] }})
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="setPackageFilter('dev')"
                                        class="px-2.5 py-1 rounded-md transition-all {{ $packageTypeFilter === 'dev' ? 'bg-white dark:bg-[#333] text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white' }}"
                                    >
                                        Dev ({{ $this->packageCounts['dev'] }})
                                    </button>
                                </div>

                                {{-- Search Input --}}
                                <div class="relative w-40 sm:w-48">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2">
                                        <flux:icon name="magnifying-glass" class="size-3 text-neutral-400" />
                                    </div>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.150ms="searchPackage"
                                        placeholder="Cari paket..."
                                        class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-neutral-50 dark:bg-[#1a1a1a] py-1 pl-7 pr-6 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 {{ $accent['ring'] }}"
                                    />
                                    @if ($searchPackage !== '')
                                        <button
                                            type="button"
                                            wire:click="$set('searchPackage', '')"
                                            class="absolute inset-y-0 right-0 flex items-center pr-1.5 text-neutral-400 hover:text-neutral-700 dark:hover:text-white"
                                        >
                                            <flux:icon name="x-mark" class="size-3" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Packages Table --}}
                        <div class="overflow-x-auto rounded-lg border border-neutral-200/80 dark:border-white/10">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-neutral-200/80 dark:border-white/10 text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 bg-neutral-50/70 dark:bg-white/5 select-none">
                                        <th class="py-2 px-3">Nama Paket</th>
                                        <th class="py-2 px-3">Versi Terinstal</th>
                                        <th class="py-2 px-3">Batasan (Constraint)</th>
                                        <th class="py-2 px-3 text-right">Tipe</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100 dark:divide-white/5">
                                    @forelse ($this->packages as $pkg)
                                        <tr class="hover:bg-neutral-50 dark:hover:bg-white/5 transition-colors">
                                            <td class="py-2 px-3 font-medium text-neutral-900 dark:text-white flex items-center gap-2">
                                                <flux:icon name="cube" class="size-3.5 text-neutral-400 shrink-0" />
                                                <span class="truncate">{{ $pkg['name'] }}</span>
                                            </td>
                                            <td class="py-2 px-3 text-emerald-600 dark:text-emerald-400 font-semibold whitespace-nowrap">
                                                {{ $pkg['version'] }}
                                            </td>
                                            <td class="py-2 px-3 text-neutral-500 whitespace-nowrap">
                                                {{ $pkg['constraint'] }}
                                            </td>
                                            <td class="py-2 px-3 text-right whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $pkg['type'] === 'prod' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-sky-500/10 text-sky-600 dark:text-sky-400' }}">
                                                    {{ $pkg['type_label'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-6 text-center text-neutral-400">
                                                Tidak ada paket yang sesuai dengan pencarian.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </main>

        {{-- Footer Status Bar --}}
        <footer class="flex shrink-0 items-center justify-between border-t border-neutral-200/90 dark:border-white/5 bg-[#f8f8f8]/90 dark:bg-[#202020]/90 px-3 py-1.5 text-xs text-neutral-500 dark:text-neutral-400 select-none">
            <div class="flex items-center gap-3">
                <span>{{ count($this->processes) }} proses</span>
                <span>•</span>
                <span>Memori: {{ $this->systemStats['memory_usage'] }}</span>
                <span>•</span>
                <span>CPU: {{ $this->systemStats['cpu_percent'] }}%</span>
            </div>
            <div class="flex items-center gap-1.5 font-medium text-emerald-600 dark:text-emerald-400">
                <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Normal</span>
            </div>
        </footer>
    </div>
</div>
