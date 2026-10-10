<footer
    x-show="statusbarVisible"
    style="grid-area: statusbar;"
    {{ $attributes->merge([
        'class' => 'flex shrink-0 items-center justify-between border-t border-neutral-200/80 dark:border-white/5 bg-[#ebebeb]/90 dark:bg-[#1f1f1f]/95 px-4 py-1.5 text-[11px] text-neutral-500 dark:text-neutral-400 backdrop-blur-md z-10 select-none'
    ]) }}
>
    {{ $slot }}
</footer>
