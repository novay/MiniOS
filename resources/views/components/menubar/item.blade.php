@props([
    'icon' => null,
    'shortcut' => null,
    'disabled' => false,
    'danger' => false,
    // Typed action/payload for dynamic menus. Do not put @js inside a Blade
    // component attribute; the attribute compiler can treat it as plain text.
    'action' => null,
    'payload' => [],
])

@php
    $baseClass = "group flex w-full items-center justify-between gap-3 px-2 py-1.5 rounded text-left transition-colors cursor-pointer select-none text-xs ";
    if ($disabled) {
        $stateClass = "opacity-40 cursor-not-allowed pointer-events-none text-neutral-400 dark:text-neutral-500 ";
    } elseif ($danger) {
        $stateClass = "text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white ";
    } else {
        $stateClass = "text-neutral-700 dark:text-neutral-200 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 dark:hover:text-white ";
    }

    $customClick = $attributes->get('@click') ?? $attributes->get('x-on:click');
    // Read dynamic menu actions from DOM data attributes so that no JavaScript
    // is constructed inside an anonymous Blade component's attribute value.
    // The custom event bubbles to the OWN desktop window, not to window/global.
    $clickExpr = $action !== null
        ? 'closeMenu(); $dispatch("minios-menu-action", { action: $el.dataset.miniosAction, payload: JSON.parse($el.dataset.miniosPayload || "{}") })'
        : ($customClick ? "closeMenu(); {$customClick}" : 'closeMenu()');
    $payloadJson = json_encode(
        is_array($payload) ? $payload : [],
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
    );
    $mergedAttributes = $attributes->except(['@click', 'x-on:click'])->merge(['class' => $baseClass . $stateClass]);
@endphp

@if ($attributes->has('href'))
    <a
        {{ $mergedAttributes }}
        @if($action !== null)
            data-minios-action="{{ $action }}"
            data-minios-payload="{{ $payloadJson }}"
        @endif
        x-on:click="{{ $clickExpr }}"
    >
        <div class="flex items-center gap-2 truncate">
            @if ($icon)
                <flux:icon :name="$icon" class="size-3.5 shrink-0 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
            @else
                <span class="w-3.5 shrink-0"></span>
            @endif
            <span class="truncate">{{ $slot }}</span>
        </div>
        @if ($shortcut)
            <span class="ml-auto pl-3 text-[10px] font-mono tracking-tight text-neutral-400 dark:text-neutral-500 group-hover:text-white/90">
                {{ $shortcut }}
            </span>
        @endif
    </a>
@else
    <button
        type="button"
        {{ $mergedAttributes }}
        @if($action !== null)
            data-minios-action="{{ $action }}"
            data-minios-payload="{{ $payloadJson }}"
        @endif
        @if(!$disabled) x-on:click="{{ $clickExpr }}" @endif
        @if($disabled) disabled @endif
    >
        <div class="flex items-center gap-2 truncate">
            @if ($icon)
                <flux:icon :name="$icon" class="size-3.5 shrink-0 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
            @else
                <span class="w-3.5 shrink-0"></span>
            @endif
            <span class="truncate">{{ $slot }}</span>
        </div>
        @if ($shortcut)
            <span class="ml-auto pl-3 text-[10px] font-mono tracking-tight text-neutral-400 dark:text-neutral-500 group-hover:text-white/90">
                {{ $shortcut }}
            </span>
        @endif
    </button>
@endif
