@props([
    'name' => null,
    'value' => null,
    'checked' => false,
    'indeterminate' => false,
    'disabled' => false,
    'all' => false,
    'size' => 'base',
    'label' => null,
    'icon' => null,
    'iconVariant' => 'outline',
    'accent' => true,
])

@php
    $showName = isset($name);

    if (! isset($name)) {
        $name = $attributes->whereStartsWith('wire:model')->first();
    }

    $sizeClasses = match ($size) {
        'xs' => 'h-6 px-2 text-[11px] rounded-[4px] gap-1.5',
        'sm' => 'h-7.5 px-2.5 text-xs rounded-md gap-1.5',
        default => 'h-8.5 px-3.5 text-xs sm:text-[13px] rounded-md gap-2',
    };

    $iconSize = match ($size) {
        'xs' => 'size-3',
        'sm' => 'size-3.5',
        default => 'size-4',
    };

    $classes = Flux::classes()
        ->add('group/btn relative inline-flex items-center font-sans font-medium text-center whitespace-nowrap select-none cursor-pointer transition-all duration-100 ease-out')
        ->add($sizeClasses)
        ->add('bg-white/80 hover:bg-neutral-50 active:bg-neutral-100 text-neutral-800')
        ->add('border border-black/[0.12] border-b-black/[0.22] shadow-[0_1px_2px_rgba(0,0,0,0.04)]')
        ->add('dark:bg-[#2d2d2d] dark:hover:bg-[#383838] dark:active:bg-[#262626] dark:text-neutral-100 dark:border-white/[0.09] dark:border-b-white/[0.05] dark:shadow-[0_1px_2px_rgba(0,0,0,0.2)]')
        ->add('focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-[var(--color-accent,#0067c0)]')
        ->add(match ($accent) {
            true => 'data-checked:bg-[var(--color-accent,#0067c0)] data-checked:text-white data-checked:border-[var(--color-accent,#0067c0)] data-checked:shadow-[inset_0_1px_0_rgba(255,255,255,0.25),0_1px_2px_rgba(0,0,0,0.15)] hover:data-checked:brightness-105',
            false => 'data-checked:bg-zinc-800 dark:data-checked:bg-white data-checked:text-white dark:data-checked:text-zinc-900 data-checked:border-zinc-800 dark:data-checked:border-white',
        })
        ->add('disabled:opacity-45 disabled:cursor-not-allowed disabled:pointer-events-none disabled:shadow-none')
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
    data-os-checkbox-buttons
    data-flux-control
    data-flux-checkbox
    data-flux-checkbox-buttons
    tabindex="0"
>
    @if (is_string($icon) && $icon !== '')
        <os:icon :name="$icon" :variant="$iconVariant" class="{{ $iconSize }} shrink-0 transition-transform group-hover/btn:scale-105" />
    @elseif ($icon)
        {{ $icon }}
    @endif

    @if ($slot->isNotEmpty() || isset($label))
        <span class="truncate">{{ $slot->isNotEmpty() ? $slot : $label }}</span>
    @endif
</ui-checkbox>
