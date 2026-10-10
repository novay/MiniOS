<nav
    {{ $attributes->merge([
        'class' => 'flex flex-1 flex-col gap-1 text-[13px] overflow-y-auto'
    ]) }}
>
    {{ $slot }}
</nav>
