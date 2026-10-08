@props([
    'label',
    'selected' => false,
])

<button
    type="button"

    {{ $attributes->class([
        'group',
        'flex',
        'flex-col',
        'items-center',
        'gap-1',
        'rounded-xl',
        'px-2',
        'py-2',
        'text-center',
        'outline-none',
        'transition-all',
        'duration-150',
        'hover:bg-white/10',
    ]) }}
    :class="{
        'w-16': (settings?.appearance?.icon_size ?? 'medium') === 'small',
        'w-20': (settings?.appearance?.icon_size ?? 'medium') === 'medium',
        'w-24': (settings?.appearance?.icon_size ?? 'medium') === 'large',
        'bg-white/20 ring-1 ring-white/30 backdrop-blur-md shadow-lg': {{ is_string($selected) ? $selected : ($selected ? 'true' : 'false') }},
    }"
>

    <div
        class="
            flex
            items-center
            justify-center

            transition-transform
            duration-150

            group-hover:scale-105
        "
        :class="{
            'size-9': (settings?.appearance?.icon_size ?? 'medium') === 'small',
            'size-12': (settings?.appearance?.icon_size ?? 'medium') === 'medium',
            'size-16': (settings?.appearance?.icon_size ?? 'medium') === 'large',
        }"
    >
        {{ $slot }}
    </div>


    <span
        class="
            desktop-label

            leading-4

            text-white
            drop-shadow-[0_1px_2px_rgba(0,0,0,0.8)]
            transition-colors
        "
        :class="{
            'text-[11px] max-w-16': (settings?.appearance?.icon_size ?? 'medium') === 'small',
            'text-[12px] max-w-20': (settings?.appearance?.icon_size ?? 'medium') === 'medium',
            'text-[13px] max-w-24': (settings?.appearance?.icon_size ?? 'medium') === 'large',
            'font-semibold': {{ is_string($selected) ? $selected : ($selected ? 'true' : 'false') }},
        }"
    >
        {{ $label }}
    </span>

</button>