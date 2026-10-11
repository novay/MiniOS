@props([
    'as' => null,
    'external' => null,
    'accent' => true,
    'variant' => null,
    'strong' => false,
])

@php
$classes = Flux::classes()
    ->add('inline font-medium')
    ->add('underline-offset-[6px] hover:decoration-current transition-colors')
    ->add(match ($variant) {
        'ghost' => 'no-underline hover:underline',
        'subtle' => 'no-underline',
        default => 'underline',
    })
    ->add('[[data-color]>&]:text-inherit [[data-color]>&]:decoration-current/20 dark:[[data-color]>&]:decoration-current/50 [[data-color]>&]:hover:decoration-current [[data-os-color]>&]:text-inherit [[data-os-color]>&]:decoration-current/20 dark:[[data-os-color]>&]:decoration-current/50 [[data-os-color]>&]:hover:decoration-current')
    ->add(match ($variant) {
        'subtle' => 'text-zinc-500 dark:text-white/70 hover:text-zinc-800 dark:hover:text-white',
        default => match ($accent) {
            true => 'text-[var(--color-accent-content)] decoration-[color-mix(in_oklab,var(--color-accent-content),transparent_80%)]',
            false => 'text-zinc-800 dark:text-white decoration-zinc-800/20 dark:decoration-white/20',
        },
    })
    ;
@endphp
<?php if ($as !== 'button') : ?><a {{ $attributes->class($classes) }} data-os-link <?php if ($external) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>{{ $slot }}</a><?php else : ?><button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }} data-os-link>{{ $slot }}</button><?php endif; ?>
