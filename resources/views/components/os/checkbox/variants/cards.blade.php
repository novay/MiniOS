@aware(['indicator'])

@props([
    'name' => null,
    'value' => null,
    'checked' => false,
    'indeterminate' => false,
    'disabled' => false,
    'all' => false,
    'label' => null,
    'description' => null,
    'icon' => null,
    'iconVariant' => 'outline',
    'indicator' => true,
    'accent' => true,
])

@php
    $showName = isset($name);

    if (! isset($name)) {
        $name = $attributes->whereStartsWith('wire:model')->first();
    }

    $indicatorPosition = $indicator ? 'end' : false;
    if ($indicator === 'start' || $indicator === 'left') {
        $indicatorPosition = 'start';
    }

    $classes = Flux::classes()
        ->add('group/card relative flex gap-3.5 flex-1 p-3.5 sm:p-4 rounded-xl cursor-pointer select-none transition-all duration-150')
        ->add($indicatorPosition === 'start' ? 'justify-start items-start' : 'justify-between items-start')
        ->add('bg-white/80 dark:bg-[#202020]/75 backdrop-blur-md')
        ->add('border border-black/[0.08] border-b-black/[0.14] dark:border-white/[0.08] dark:border-b-black/40')
        ->add('shadow-[0_1px_2px_rgba(0,0,0,0.03)] dark:shadow-[0_1px_3px_rgba(0,0,0,0.25)]')
        ->add('hover:border-black/[0.18] dark:hover:border-white/[0.18] hover:bg-white dark:hover:bg-[#282828]')
        ->add(match ($accent) {
            true => 'data-checked:border-[var(--color-accent,#0067c0)] data-checked:bg-[var(--color-accent)]/[0.04] dark:data-checked:bg-[var(--color-accent)]/[0.1] data-checked:shadow-xs',
            false => 'data-checked:border-zinc-800 dark:data-checked:border-white data-checked:bg-zinc-50 dark:data-checked:bg-white/10',
        })
        ->add('disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none')
        ;
@endphp

<ui-checkbox
    {{ $attributes->class($classes) }}
    @if ($showName && $name) name="{{ $name }}" @endif
    @if ($value !== null) value="{{ $value }}" @endif
    @if ($checked) checked @endif
    @if ($disabled) disabled @endif
    @if ($indeterminate) data-indeterminate @endif
    @if ($all) all @endif
    data-os-control
    data-os-checkbox
    data-os-checkbox-cards
    data-flux-control
    data-flux-checkbox
    data-flux-checkbox-cards
    tabindex="0"
>
    @if ($label || $description)
        @if ($indicatorPosition === 'start')
            <os:checkbox.indicator class="mt-0.5" />
        @endif

        <div class="flex-1 flex items-start gap-3 min-w-0">
            @if (is_string($icon) && $icon !== '')
                <div class="shrink-0 mt-0.5 text-zinc-500 group-hover/card:text-zinc-800 dark:text-zinc-400 dark:group-hover/card:text-zinc-200 [ui-checkbox[data-checked]_&]:text-[var(--color-accent,#0067c0)] dark:[ui-checkbox[data-checked]_&]:text-white transition-colors">
                    <os:icon :name="$icon" :variant="$iconVariant" class="size-5" />
                </div>
            @elseif ($icon)
                <div class="shrink-0 mt-0.5">
                    {{ $icon }}
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold text-zinc-800 dark:text-white leading-tight">
                    {{ $slot->isNotEmpty() ? $slot : $label }}
                </div>

                @if ($description)
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 leading-relaxed">
                        {{ $description }}
                    </div>
                @endif
            </div>
        </div>

        @if ($indicatorPosition === 'end')
            <os:checkbox.indicator class="mt-0.5 shrink-0" />
        @endif
    @else
        {{ $slot }}
    @endif
</ui-checkbox>
