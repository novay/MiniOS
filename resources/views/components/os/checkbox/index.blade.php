@aware(['variant'])

@props([
    'variant' => 'default',
    'all' => false,
])

@php
    $effectiveVariant = match ($variant) {
        'cards' => 'cards',
        'buttons' => 'buttons',
        'pills' => 'pills',
        default => 'default',
    };
@endphp

@switch ($effectiveVariant)
    @case('cards')
        <os:checkbox.variants.cards :$all {{ $attributes }}>{{ $slot }}</os:checkbox.variants.cards>
        @break
    @case('buttons')
        <os:checkbox.variants.buttons :$all {{ $attributes }}>{{ $slot }}</os:checkbox.variants.buttons>
        @break
    @case('pills')
        <os:checkbox.variants.pills :$all {{ $attributes }}>{{ $slot }}</os:checkbox.variants.pills>
        @break
    @default
        <os:checkbox.variants.default :$all {{ $attributes }}>{{ $slot }}</os:checkbox.variants.default>
@endswitch
