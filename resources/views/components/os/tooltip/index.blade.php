@props([
    'interactive' => null,
    'position' => 'top',
    'align' => 'center',
    'content' => null,
    'kbd' => null,
    'toggleable' => null,
])

@php
// Support adding the .self modifier to the wire:model directive...
if (($wireModel = $attributes->wire('model')) && $wireModel->directive && ! $wireModel->hasModifier('self')) {
    unset($attributes[$wireModel->directive]);

    $wireModel->directive .= '.self';

    $attributes = $attributes->merge([$wireModel->directive => $wireModel->value]);
}
@endphp

@if ($toggleable)
    <ui-dropdown position="{{ $position }} {{ $align }}" {{ $attributes }} data-flux-tooltip data-fluent-tooltip>
        {{ $slot }}

        @if ($content !== null)
            <os:tooltip.content :$kbd>{{ $content }}</os:tooltip.content>
        @endif
    </ui-dropdown>
@else
    <ui-tooltip position="{{ $position }} {{ $align }}" {{ $attributes }} data-flux-tooltip data-fluent-tooltip @if ($interactive) interactive @endif>
        {{ $slot }}

        @if ($content !== null)
            <os:tooltip.content :$kbd>{{ $content }}</os:tooltip.content>
        @endif
    </ui-tooltip>
@endif
