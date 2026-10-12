@props([
    'name' => null,
    'value' => null,
    'checked' => false,
    'indeterminate' => false,
    'disabled' => false,
    'all' => false,
    'label' => null,
    'description' => null,
])

@php
    $showName = isset($name);

    if (! isset($name)) {
        $name = $attributes->whereStartsWith('wire:model')->first();
    }

    $hasDescription = filled($description);
    $alignClass = $hasDescription ? 'items-start' : 'items-center';
    $boxOffset = $hasDescription ? 'mt-0.5' : '';

    $controlClasses = Flux::classes()
        ->add('inline-flex size-[1.125rem] rounded-[4px] outline-offset-2 cursor-pointer shrink-0')
        ->add($boxOffset)
        ->add('focus-visible:outline-2 focus-visible:outline-[var(--color-accent,#0067c0)] focus-visible:outline-offset-2')
        ->add('disabled:cursor-not-allowed disabled:opacity-50')
        ;
@endphp

@if ($label || $description || $slot->isNotEmpty())
    <label class="group/os-checkbox relative inline-flex {{ $alignClass }} gap-2.5 text-start cursor-pointer select-none [&:has(ui-checkbox[disabled])]:cursor-not-allowed [&:has(ui-checkbox[disabled])]:opacity-60" data-os-field>
        <ui-checkbox
            {{ $attributes->class($controlClasses) }}
            @if ($showName && $name) name="{{ $name }}" @endif
            @if ($value !== null) value="{{ $value }}" @endif
            @if ($checked) checked @endif
            @if ($disabled) disabled @endif
            @if ($indeterminate) data-indeterminate @endif
            @if ($all) all @endif
            data-os-control
            data-os-checkbox
            data-flux-control
            data-flux-checkbox
            tabindex="0"
        >
            <os:checkbox.indicator />
        </ui-checkbox>

        <div class="flex-1 min-w-0">
            @if ($slot->isNotEmpty() || $label)
                <div class="text-sm font-medium text-zinc-800 dark:text-zinc-100 leading-tight transition-colors group-hover/os-checkbox:text-zinc-900 dark:group-hover/os-checkbox:text-white" data-os-label>
                    {{ $slot->isNotEmpty() ? $slot : $label }}
                </div>
            @endif

            @if ($description)
                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 leading-normal" data-os-description>
                    {{ $description }}
                </div>
            @endif
        </div>
    </label>
@else
    <ui-checkbox
        {{ $attributes->class($controlClasses) }}
        @if ($showName && $name) name="{{ $name }}" @endif
        @if ($value !== null) value="{{ $value }}" @endif
        @if ($checked) checked @endif
        @if ($disabled) disabled @endif
        @if ($indeterminate) data-indeterminate @endif
        @if ($all) all @endif
        data-os-control
        data-os-checkbox
        data-flux-control
        data-flux-checkbox
        tabindex="0"
    >
        <os:checkbox.indicator />
    </ui-checkbox>
@endif
