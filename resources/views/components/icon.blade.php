@props([
    'name',
    'class' => 'size-6',
])

@switch($name)

    @case('browser')
        <svg {{ $attributes->merge(['class' => $class]) }} xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 190.5 190.5"><path d="M0 0h190.5v190.5H0z" fill="none"/><path fill="#fff" d="M95.252 142.873c26.304 0 47.627-21.324 47.627-47.628s-21.323-47.628-47.627-47.628s-47.627 21.324-47.627 47.628s21.323 47.628 47.627 47.628"/><path fill="#229342" d="m54.005 119.07l-41.24-71.43a95.23 95.23 0 0 0-.003 95.25a95.23 95.23 0 0 0 82.496 47.61l41.24-71.43v-.011a47.6 47.6 0 0 1-17.428 17.443a47.62 47.62 0 0 1-47.632.007a47.6 47.6 0 0 1-17.433-17.437z"/><path fill="#fbc116" d="m136.495 119.067l-41.239 71.43a95.23 95.23 0 0 0 82.489-47.622A95.24 95.24 0 0 0 190.5 95.248a95.24 95.24 0 0 0-12.772-47.623H95.249l-.01.007a47.6 47.6 0 0 1 23.819 6.372a47.6 47.6 0 0 1 17.439 17.431a47.62 47.62 0 0 1-.001 47.633z"/><path fill="#1a73e8" d="M95.252 132.961c20.824 0 37.705-16.881 37.705-37.706S116.076 57.55 95.252 57.55S57.547 74.431 57.547 95.255s16.881 37.706 37.705 37.706"/><path fill="#e33b2e" d="M95.252 47.628h82.479A95.24 95.24 0 0 0 142.87 12.76A95.23 95.23 0 0 0 95.245 0a95.2 95.2 0 0 0-47.623 12.767a95.23 95.23 0 0 0-34.856 34.872l41.24 71.43l.011.006a47.62 47.62 0 0 1-.015-47.633a47.61 47.61 0 0 1 41.252-23.815z"/></svg>
    @break


    @case('files')
        <svg
            {{ $attributes->merge(['class' => $class]) }}
            viewBox="0 0 48 48"
            fill="none"
        >
            <path
                d="M7 14C7 11.8 8.8 10 11 10H20L24 14H37C39.2 14 41 15.8 41 18V35C41 37.2 39.2 39 37 39H11C8.8 39 7 37.2 7 35V14Z"
                fill="#E9A14A"
            />

            <path
                d="M7 18H41V35C41 37.2 39.2 39 37 39H11C8.8 39 7 37.2 7 35V18Z"
                fill="#D97B29"
            />

            <path
                d="M10 21H38"
                stroke="white"
                stroke-opacity=".35"
                stroke-width="2"
            />
        </svg>
    @break

    @case('calculator')
        <img src="{{ asset('minios/images/ic-calculator.webp') }}" alt="" {{ $attributes->merge(['class' => $class]) }} />
    @break

    @case('activity-monitor')
        <img src="{{ asset('minios/images/ic-monitor.webp') }}" alt="" {{ $attributes->merge(['class' => $class]) }} />
    @break


    @case('terminal')
        <img src="{{ asset('minios/images/ic-terminal.webp') }}" alt="" {{ $attributes->merge(['class' => $class]) }} />
    @break


    @case('settings')
        <img src="{{ asset('minios/images/ic-settings.webp') }}" alt="" {{ $attributes->merge(['class' => $class]) }} />
    @break


    @case('control-panel')
        <div {{ $attributes->merge(['class' => $class . ' flex items-center justify-center rounded-2xl bg-gradient-to-br from-slate-800 via-sky-900 to-indigo-950 shadow-md text-white p-1 border border-white/15']) }}>
            <svg class="size-full p-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="1.5" fill="#38bdf8" fill-opacity="0.9" stroke="#38bdf8" />
                <rect x="14" y="3" width="7" height="7" rx="1.5" fill="#818cf8" fill-opacity="0.9" stroke="#818cf8" />
                <rect x="3" y="14" width="7" height="7" rx="1.5" fill="#34d399" fill-opacity="0.9" stroke="#34d399" />
                <circle cx="17.5" cy="17.5" r="3.5" fill="#f43f5e" fill-opacity="0.9" stroke="#f43f5e" />
                <path d="M17.5 16v3" stroke="#fff" stroke-width="1.5" />
                <path d="M16 17.5h3" stroke="#fff" stroke-width="1.5" />
            </svg>
        </div>
    @break


    @case('about')
    @case('minios')
    @case('logo')
        <img src="{{ asset('minios/images/logo.png') }}" alt="MiniOS" {{ $attributes->merge(['class' => $class]) }} />
    @break

    @case('apps')
        <svg
            {{ $attributes->merge(['class' => $class]) }}
            viewBox="0 0 24 24"
            fill="currentColor"
        >
            @foreach ([5, 12, 19] as $x)
                @foreach ([5, 12, 19] as $y)
                    <circle
                        cx="{{ $x }}"
                        cy="{{ $y }}"
                        r="1.8"
                    />
                @endforeach
            @endforeach
        </svg>
    @break


    @case('home')
        <svg
            {{ $attributes->merge(['class' => $class]) }}
            viewBox="0 0 48 48"
            fill="none"
        >
            <path
                d="M7 22L24 8L41 22V39H29V28H19V39H7V22Z"
                fill="#E9A14A"
            />

            <path
                d="M16 39V24H32V39"
                fill="#D97B29"
            />
        </svg>
    @break


    @case('trash')
        <svg
            {{ $attributes->merge(['class' => $class]) }}
            viewBox="0 0 48 48"
            fill="none"
        >
            <path
                d="M13 15H35L33 40H15L13 15Z"
                fill="#D8D8D8"
            />

            <path
                d="M11 12H37"
                stroke="#F3F3F3"
                stroke-width="4"
                stroke-linecap="round"
            />

            <path
                d="M19 9H29"
                stroke="#F3F3F3"
                stroke-width="4"
                stroke-linecap="round"
            />

            <path
                d="M20 20V34M28 20V34"
                stroke="#999"
                stroke-width="2"
                stroke-linecap="round"
            />
        </svg>
    @break

    @case('todo')
        <div {{ $attributes->merge(['class' => $class . ' flex items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-md text-white']) }}>
            <svg class="size-2/3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 11l3 3L22 4" />
                <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
            </svg>
        </div>
    @break

    @default
        @if (str_starts_with($name, 'http') || str_starts_with($name, '/') || str_ends_with($name, '.png') || str_ends_with($name, '.webp') || str_ends_with($name, '.svg'))
            <img src="{{ $name }}" alt="" {{ $attributes->merge(['class' => $class]) }} />
        @else
            <div {{ $attributes->merge(['class' => $class . ' flex items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow text-white p-1']) }}>
                <flux:icon :name="$name" class="size-full" />
            </div>
        @endif
    @break

@endswitch