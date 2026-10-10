<div
    x-data="{
        scrollToBottom() {
            $nextTick(() => {
                const el = $refs.terminalContainer;
                if (el) el.scrollTop = el.scrollHeight;
            });
        }
    }"
    x-init="scrollToBottom()"
    @terminal-scrolled.window="scrollToBottom()"
    @click="$refs.terminalInput?.focus()"
    class="flex h-full w-full flex-col bg-[#16161a] text-neutral-100 font-mono text-xs overflow-hidden select-none"
>
    {{-- macOS Terminal Top Bar --}}
    <div class="flex items-center justify-between border-b border-white/10 bg-[#212126] px-4 py-2 text-neutral-400 text-[11px] font-sans">
        <div class="flex items-center gap-2">
            <svg class="size-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="font-medium text-neutral-200">minios — zsh — 80x24</span>
        </div>
        <div class="text-[10px] text-neutral-500 font-mono">zsh 5.9</div>
    </div>

    {{-- Terminal Output Container --}}
    <div
        x-ref="terminalContainer"
        class="flex-1 overflow-y-auto p-4 space-y-2 cursor-text scrollbar-thin scrollbar-thumb-white/10"
    >
        @foreach ($history as $item)
            @if ($item['type'] === 'welcome')
                <div class="text-sky-400/90 whitespace-pre-wrap leading-relaxed">{{ $item['output'] }}</div>
            @elseif ($item['type'] === 'input')
                <div class="flex items-center gap-2">
                    <span class="text-emerald-400 font-bold">minios@simpora</span>:<span class="text-sky-400 font-bold">~</span>$
                    <span class="text-neutral-100 font-semibold">{{ $item['command'] }}</span>
                </div>
            @elseif ($item['type'] === 'output')
                <div class="text-neutral-300 whitespace-pre-wrap leading-relaxed pl-2 border-l-2 border-emerald-500/40">{{ $item['output'] }}</div>
            @elseif ($item['type'] === 'error')
                <div class="text-rose-400 whitespace-pre-wrap leading-relaxed pl-2 border-l-2 border-rose-500/40">{{ $item['output'] }}</div>
            @endif
        @endforeach

        {{-- Active Input Command Prompt Line --}}
        <form wire:submit.prevent="executeCommand" class="flex items-center gap-2 pt-1">
            <span class="text-emerald-400 font-bold flex-shrink-0">minios@simpora</span>:<span class="text-sky-400 font-bold flex-shrink-0">~</span>$
            <input
                x-ref="terminalInput"
                wire:model="command"
                type="text"
                autofocus
                autocomplete="off"
                spellcheck="false"
                class="flex-1 bg-transparent border-none p-0 text-xs text-neutral-100 focus:outline-none focus:ring-0 font-mono"
            />
        </form>
    </div>
</div>
