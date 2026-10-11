@props([
    'variant' => null,
    'color' => null,
    'icon' => null,
    'iconVariant' => 'mini',
    'heading' => null,
    'text' => null,
    'inline' => false,
    'dismissible' => false,
    'actions' => null,
    'controls' => null,
    'content' => null,
])

@php
    // Map variant to standard color and severity
    $effectiveVariant = match ($variant) {
        'primary', 'info', 'informational' => 'info',
        'success' => 'success',
        'warning', 'caution' => 'warning',
        'danger', 'critical', 'error' => 'danger',
        'secondary', 'neutral', 'zinc' => 'secondary',
        default => $variant,
    };

    $effectiveColor = $color ?? match ($effectiveVariant) {
        'success' => 'emerald',
        'warning' => 'amber',
        'danger' => 'rose',
        'secondary' => 'zinc',
        'info' => 'sky',
        default => 'sky',
    };

    // Auto default icon based on status severity if icon is null
    if ($icon === null) {
        $icon = match ($effectiveVariant ?? $effectiveColor) {
            'success', 'emerald', 'green' => 'check-circle',
            'warning', 'amber', 'yellow' => 'exclamation-triangle',
            'danger', 'rose', 'red' => 'exclamation-circle',
            'secondary', 'zinc', 'neutral' => 'information-circle',
            default => 'information-circle',
        };
    } elseif ($icon === 'none' || $icon === false || $icon === '') {
        $icon = null;
    }

    // Windows 11 Fluent Kit color schemes (WinUI 3 InfoBar)
    $colorClasses = match ($effectiveColor) {
        'emerald', 'green' => [
            'bg-emerald-50/90 dark:bg-emerald-950/40 border-emerald-200/90 dark:border-emerald-800/60 text-emerald-950 dark:text-emerald-100',
            '[--callout-heading:var(--color-emerald-950)] dark:[--callout-heading:var(--color-emerald-100)]',
            '[--callout-text:var(--color-emerald-900)] dark:[--callout-text:var(--color-emerald-200)]',
            '[--callout-icon:var(--color-emerald-600)] dark:[--callout-icon:var(--color-emerald-400)]',
            '[--callout-border:var(--color-emerald-200)] dark:[--callout-border:var(--color-emerald-800)]',
        ],
        'amber', 'yellow' => [
            'bg-amber-50/90 dark:bg-amber-950/40 border-amber-200/90 dark:border-amber-800/60 text-amber-950 dark:text-amber-100',
            '[--callout-heading:var(--color-amber-950)] dark:[--callout-heading:var(--color-amber-100)]',
            '[--callout-text:var(--color-amber-900)] dark:[--callout-text:var(--color-amber-200)]',
            '[--callout-icon:var(--color-amber-600)] dark:[--callout-icon:var(--color-amber-400)]',
            '[--callout-border:var(--color-amber-200)] dark:[--callout-border:var(--color-amber-800)]',
        ],
        'rose', 'red' => [
            'bg-rose-50/90 dark:bg-rose-950/40 border-rose-200/90 dark:border-rose-800/60 text-rose-950 dark:text-rose-100',
            '[--callout-heading:var(--color-rose-950)] dark:[--callout-heading:var(--color-rose-100)]',
            '[--callout-text:var(--color-rose-900)] dark:[--callout-text:var(--color-rose-200)]',
            '[--callout-icon:var(--color-rose-600)] dark:[--callout-icon:var(--color-rose-400)]',
            '[--callout-border:var(--color-rose-200)] dark:[--callout-border:var(--color-rose-800)]',
        ],
        'zinc', 'neutral', 'gray' => [
            'bg-zinc-50/90 dark:bg-zinc-900/60 border-zinc-200/90 dark:border-white/10 text-zinc-900 dark:text-zinc-100',
            '[--callout-heading:var(--color-zinc-900)] dark:[--callout-heading:var(--color-zinc-100)]',
            '[--callout-text:var(--color-zinc-700)] dark:[--callout-text:var(--color-zinc-300)]',
            '[--callout-icon:var(--color-zinc-500)] dark:[--callout-icon:var(--color-zinc-400)]',
            '[--callout-border:var(--color-zinc-200)] dark:[--callout-border:var(--color-zinc-800)]',
        ],
        'purple', 'violet' => [
            'bg-purple-50/90 dark:bg-purple-950/40 border-purple-200/90 dark:border-purple-800/60 text-purple-950 dark:text-purple-100',
            '[--callout-heading:var(--color-purple-950)] dark:[--callout-heading:var(--color-purple-100)]',
            '[--callout-text:var(--color-purple-900)] dark:[--callout-text:var(--color-purple-200)]',
            '[--callout-icon:var(--color-purple-600)] dark:[--callout-icon:var(--color-purple-400)]',
            '[--callout-border:var(--color-purple-200)] dark:[--callout-border:var(--color-purple-800)]',
        ],
        'teal', 'cyan' => [
            'bg-teal-50/90 dark:bg-teal-950/40 border-teal-200/90 dark:border-teal-800/60 text-teal-950 dark:text-teal-100',
            '[--callout-heading:var(--color-teal-950)] dark:[--callout-heading:var(--color-teal-100)]',
            '[--callout-text:var(--color-teal-900)] dark:[--callout-text:var(--color-teal-200)]',
            '[--callout-icon:var(--color-teal-600)] dark:[--callout-icon:var(--color-teal-400)]',
            '[--callout-border:var(--color-teal-200)] dark:[--callout-border:var(--color-teal-800)]',
        ],
        default => [ // sky / blue
            'bg-sky-50/90 dark:bg-sky-950/40 border-sky-200/90 dark:border-sky-800/60 text-sky-950 dark:text-sky-100',
            '[--callout-heading:var(--color-sky-950)] dark:[--callout-heading:var(--color-sky-100)]',
            '[--callout-text:var(--color-sky-900)] dark:[--callout-text:var(--color-sky-200)]',
            '[--callout-icon:var(--color-sky-600)] dark:[--callout-icon:var(--color-sky-400)]',
            '[--callout-border:var(--color-sky-200)] dark:[--callout-border:var(--color-sky-800)]',
        ],
    };

    $classes = [
        'relative flex items-start gap-3 p-3 sm:px-4 sm:py-3 rounded-lg border text-xs sm:text-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] dark:shadow-[0_1px_3px_rgba(0,0,0,0.2)] transition-all',
        $inline ? 'sm:items-center' : '',
        ...$colorClasses,
    ];
@endphp

<div
    {{ $attributes->class($classes) }}
    data-fluent-callout="true"
    data-flux-callout="true"
    @if ($dismissible)
        x-data="{ show: true }"
        x-show="show"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    @endif
>
    {{-- Leading Icon --}}
    @if (is_string($icon) && $icon !== '')
        <div class="shrink-0 text-[var(--callout-icon)] {{ $inline ? 'sm:self-center pt-0.5 sm:pt-0' : 'pt-0.5' }}">
            <os:icon :name="$icon" class="size-5 shrink-0" />
        </div>
    @elseif ($icon)
        <div class="shrink-0 text-[var(--callout-icon)] {{ $inline ? 'sm:self-center pt-0.5 sm:pt-0' : 'pt-0.5' }}">
            {{ $icon }}
        </div>
    @endif

    {{-- Content & Actions Wrapper --}}
    @if ($inline)
        <div class="flex-1 min-w-0 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex-1 min-w-0 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2.5" data-slot="content">
                @if ($heading)
                    <os:callout.heading>{{ $heading }}</os:callout.heading>
                @endif

                @if ($text)
                    <os:callout.text>{{ $text }}</os:callout.text>
                @endif

                {{ $content ?? $slot }}
            </div>

            @if ($actions)
                <div class="flex items-center gap-2 shrink-0 sm:ps-2" data-slot="actions">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @else
        <div class="flex-1 min-w-0 flex flex-col gap-1" data-slot="content">
            @if ($heading)
                <os:callout.heading>{{ $heading }}</os:callout.heading>
            @endif

            @if ($text)
                <os:callout.text>{{ $text }}</os:callout.text>
            @endif

            {{ $content ?? $slot }}

            @if ($actions)
                <div class="flex items-center gap-2 pt-1.5" data-slot="actions">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    {{-- Controls / Dismiss Button --}}
    @if ($controls)
        <div class="shrink-0 -me-1 -mt-1 sm:-mt-0.5" data-slot="controls">
            {{ $controls }}
        </div>
    @elseif ($dismissible)
        <div class="shrink-0 -me-1 -mt-1 sm:-mt-0.5" data-slot="controls">
            <button
                type="button"
                @click="show = false"
                class="inline-flex items-center justify-center size-6 rounded-[4px] text-neutral-500 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-100 hover:bg-black/5 dark:hover:bg-white/10 active:bg-black/10 dark:active:bg-white/15 transition-colors focus:outline-none"
                aria-label="Close"
            >
                <flux:icon name="x-mark" variant="mini" class="size-4" />
            </button>
        </div>
    @endif
</div>
