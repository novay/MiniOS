@props([
    'findable' => false,
])

<ui-tab-group {{ $attributes->class('block') }} @if ($findable) findable @endif data-os-tab-group>
    {{ $slot }}
</ui-tab-group>
