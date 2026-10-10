<div
    style="grid-area: menu;"
    {{ $attributes->merge([
        'class' => 'minios-desktop-menu relative z-40 flex h-8 shrink-0 items-center justify-between border-b border-neutral-200/80 dark:border-white/5 bg-white/70 dark:bg-[#1c1c1c]/90 px-2 text-xs backdrop-blur-md select-none'
    ]) }}
>
    {{ $slot }}
</div>
