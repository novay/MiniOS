@props([
    'variant' => 'default',
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
        <os:checkbox.group.variants.cards {{ $attributes }}>{{ $slot }}</os:checkbox.group.variants.cards>
        @break
    @case('buttons')
        <os:checkbox.group.variants.buttons {{ $attributes }}>{{ $slot }}</os:checkbox.group.variants.buttons>
        @break
    @case('pills')
        <os:checkbox.group.variants.pills {{ $attributes }}>{{ $slot }}</os:checkbox.group.variants.pills>
        @break
    @default
        <os:checkbox.group.variants.default {{ $attributes }}>{{ $slot }}</os:checkbox.group.variants.default>
@endswitch
