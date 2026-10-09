@props([
    'name',
    'class' => 'size-6',
])

@switch($name)

    @case('browser')
        <img {{ $attributes->merge(['class' => $class]) }} src="https://demo.pixelcave.com/freebies/45-windows-11-tailwind/assets/icons/edge.png">
    @break


    @case('files')
        <img {{ $attributes->merge(['class' => $class]) }} src="https://demo.pixelcave.com/freebies/45-windows-11-tailwind/assets/icons/folder.png">
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

    @case('windows')
    @case('start')
        <svg
            {{ $attributes->merge(['class' => $class]) }}
            viewBox="0 0 88 88"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path d="M0 0H40V40H0V0Z" fill="#0078D4" />
            <path d="M48 0H88V40H48V0Z" fill="#0078D4" />
            <path d="M0 48H40V88H0V48Z" fill="#0078D4" />
            <path d="M48 48H88V88H48V48Z" fill="#0078D4" />
        </svg>
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