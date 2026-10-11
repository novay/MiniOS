@props([
    'external' => false,
])

<a {{ $attributes->class('inline-flex items-center gap-1 font-semibold underline underline-offset-4 decoration-current/40 hover:decoration-current text-[var(--callout-heading)] transition-colors') }} @if ($external) target="_blank" rel="noopener noreferrer" @endif>{{ $slot }}</a>
