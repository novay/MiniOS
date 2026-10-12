@props([
    'label' => 'Select all',
    'description' => null,
])

<os:checkbox all :label="$label" :description="$description" {{ $attributes }} />
