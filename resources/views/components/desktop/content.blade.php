<main
    style="grid-area: content;"
    {{ $attributes->merge([
        'class' => '@container flex flex-1 flex-col min-w-0 min-h-0 h-full overflow-y-auto bg-[#f3f3f3] dark:bg-[#1f1f1f]'
    ]) }}
>
    {{ $slot }}
</main>
