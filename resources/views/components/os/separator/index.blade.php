@props([
    'orientation' => null,
    'vertical' => false,
    'variant' => null,
    'faint' => false,
    'text' => null,
    'align' => 'center',
])

@php
$orientation ??= $vertical ? 'vertical' : 'horizontal';

if ($faint && ! $variant) {
    $variant = 'subtle';
}

$classes = Flux::classes('border-0 [print-color-adjust:exact]')
    ->add(match ($variant) {
        'subtle' => 'bg-zinc-800/5 dark:bg-white/10',
        default => 'bg-zinc-800/15 dark:bg-white/20',
    })
    ->add(match ($orientation) {
        'horizontal' => 'h-px w-full',
        'vertical' => 'w-px',
    })
    ;

if ($orientation === 'vertical') {
    $hasClass = (string) $attributes->get('class');
    $hasSelf = preg_match('/\bself-(auto|start|end|center|stretch|baseline)\b/', $hasClass);

    if (! $hasSelf) {
        $hasHeight = preg_match('/\b(h-|min-h-|max-h-)/', $hasClass);
        $classes = $classes->add($hasHeight ? 'self-center' : 'self-stretch');
    }
}

$separatorText = $text ?? ($slot->isNotEmpty() ? $slot : null);
@endphp

<?php if ($separatorText): ?>
    <div data-orientation="{{ $orientation }}" role="separator" {{ $attributes->class('flex items-center w-full') }} data-os-separator>
        <?php if ($align !== 'left' && $align !== 'start'): ?>
            <div class="{{ $classes }} grow"></div>
        <?php endif; ?>

        <span class="shrink font-medium text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 whitespace-nowrap {{ match($align) { 'left', 'start' => 'me-4', 'right', 'end' => 'ms-4', default => 'mx-4' } }}">{{ $separatorText }}</span>

        <?php if ($align !== 'right' && $align !== 'end'): ?>
            <div class="{{ $classes }} grow"></div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div data-orientation="{{ $orientation }}" role="separator" {{ $attributes->class($classes, 'shrink-0') }} data-os-separator></div>
<?php endif; ?>
