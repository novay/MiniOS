@props([
    'name' => null,
    'value' => null,
    'checked' => false,
    'indeterminate' => false,
    'disabled' => false,
    'all' => false,
    'label' => null,
    'accent' => true,
])

@php
    $showName = isset($name);

    if (! isset($name)) {
        $name = $attributes->whereStartsWith('wire:model')->first();
    }

    $classes = Flux::classes()
        ->add('inline-flex items-center justify-center gap-1.5 whitespace-nowrap py-1.5 px-3 rounded-full text-xs sm:text-[13px] font-medium leading-tight cursor-pointer select-none transition-all duration-150')
        ->add('bg-zinc-800/6 dark:bg-white/10 hover:bg-zinc-800/10 dark:hover:bg-white/15 text-zinc-700 dark:text-zinc-300')
        ->add('border border-black/[0.04] dark:border-white/[0.06]')
        ->add(match ($accent) {
            true => 'data-checked:bg-[var(--color-accent,#0067c0)] data-checked:text-white data-checked:border-[var(--color-accent,#0067c0)] hover:data-checked:brightness-105 shadow-2xs',
            false => 'data-checked:bg-zinc-800 dark:data-checked:white data-checked:text-white dark:data-checked:text-zinc-900',
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
    data-os-checkbox-pills
    data-flux-control
    data-flux-checkbox
    data-flux-checkbox-pills
    tabindex="0"
>
    {{ $slot->isNotEmpty() ? $slot : $label }}
</ui-checkbox>
