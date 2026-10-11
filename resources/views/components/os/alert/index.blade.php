<os:callout {{ $attributes }}>
    @if (isset($icon)) <x-slot:icon>{{ $icon }}</x-slot:icon> @endif
    @if (isset($actions)) <x-slot:actions>{{ $actions }}</x-slot:actions> @endif
    @if (isset($controls)) <x-slot:controls>{{ $controls }}</x-slot:controls> @endif
    {{ $slot }}
</os:callout>
