@props([
    'name' => null,
    'label' => null,
    'description' => null,
    'badge' => null,
    'disabled' => false,
])

@php
    $showName = isset($name);

    if (! isset($name)) {
        $name = $attributes->whereStartsWith('wire:model')->first();
    }

    if (($wireModel = $attributes->wire('model')) && $wireModel->directive && ! $wireModel->hasModifier('self')) {
        unset($attributes[$wireModel->directive]);
        $wireModel->directive .= '.self';
        $attributes = $attributes->merge([$wireModel->directive => $wireModel->value]);
    }

    $groupClasses = Flux::classes()
        ->add('flex flex-wrap gap-2.5')
        ;
@endphp

<div class="space-y-2.5" data-os-field>
    @if ($label)
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100 leading-tight" data-os-label>
                {{ $label }}
            </span>
            @if ($badge)
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium bg-zinc-800/6 dark:bg-white/10 text-zinc-700 dark:text-zinc-300">
                    {{ $badge }}
                </span>
            @endif
        </div>
    @endif

    @if ($description)
        <p class="text-xs text-zinc-500 dark:text-zinc-400 -mt-1 leading-normal" data-os-description>
            {{ $description }}
        </p>
    @endif

    <ui-checkbox-group
        {{ $attributes->class($groupClasses) }}
        @if ($showName && $name) name="{{ $name }}" @endif
        @if ($disabled) disabled @endif
        data-os-control
        data-os-checkbox-group
        data-os-checkbox-group-pills
        data-flux-control
        data-flux-checkbox-group
        data-flux-checkbox-group-pills
    >
        {{ $slot }}
    </ui-checkbox-group>
</div>
