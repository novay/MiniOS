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

    @case('preview')
        <div {{ $attributes->merge(['class' => $class . ' flex items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 via-teal-600 to-cyan-700 shadow-md text-white p-1 border border-white/20']) }}>
            <svg class="size-full p-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" />
                <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor" stroke="none" />
                <path d="M21 15l-5-5L5 21" stroke="currentColor" />
            </svg>
        </div>
    @break 
    
    @case('textedit')
        <div {{ $attributes->merge(['class' => $class . ' flex items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 via-blue-600 to-indigo-700 shadow-md text-white p-1 border border-white/20']) }}>
            <svg class="size-full p-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
                <line x1="10" y1="9" x2="8" y2="9" />
            </svg>
        </div>
    @break

    @case('player')
        <div {{ $attributes->merge(['class' => $class . ' flex items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 via-fuchsia-600 to-rose-600 shadow-md text-white p-1 border border-white/20']) }}>
            <svg class="size-full p-1" viewBox="0 0 24 24" fill="currentColor">
                <polygon points="6 4 20 12 6 20 6 4" />
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
    @case('trash-empty')
        <svg
            {{ $attributes->merge(['class' => $class]) }}
            viewBox="0 0 48 48"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <defs>
                <linearGradient id="binBodyEmpty" x1="12" y1="14" x2="36" y2="44" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#E2E8F0" stop-opacity="0.9" />
                    <stop offset="100%" stop-color="#CBD5E1" stop-opacity="0.95" />
                </linearGradient>
                <linearGradient id="binRimEmpty" x1="10" y1="11" x2="38" y2="15" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#F8FAFC" />
                    <stop offset="100%" stop-color="#94A3B8" />
                </linearGradient>
                <linearGradient id="binInnerEmpty" x1="14" y1="12" x2="34" y2="16" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#64748B" />
                    <stop offset="100%" stop-color="#94A3B8" />
                </linearGradient>
            </defs>

            {{-- Inner depth --}}
            <ellipse cx="24" cy="14" rx="13.5" ry="3.8" fill="url(#binInnerEmpty)" />

            {{-- Bin Body --}}
            <path
                d="M12 14.5 L15 41 C15.2 42.4 16.4 43.5 17.8 43.5 H30.2 C31.6 43.5 32.8 42.4 33 41 L36 14.5 Z"
                fill="url(#binBodyEmpty)"
                stroke="#94A3B8"
                stroke-width="1.2"
                stroke-linejoin="round"
            />

            {{-- Vertical ribs --}}
            <path d="M18.5 17 L20 39.5" stroke="#94A3B8" stroke-width="1.2" stroke-linecap="round" opacity="0.6" />
            <path d="M24 17.5 L24 40" stroke="#94A3B8" stroke-width="1.2" stroke-linecap="round" opacity="0.6" />
            <path d="M29.5 17 L28 39.5" stroke="#94A3B8" stroke-width="1.2" stroke-linecap="round" opacity="0.6" />

            {{-- Bin Outer Rim --}}
            <ellipse cx="24" cy="13.5" rx="14.5" ry="4" fill="none" stroke="url(#binRimEmpty)" stroke-width="2" />
            <ellipse cx="24" cy="13" rx="14.2" ry="3.6" fill="none" stroke="#F8FAFC" stroke-width="0.8" opacity="0.8" />

            {{-- Recycle Mobius Arrows Accent --}}
            <path
                d="M21.5 27 L24 23 L26.5 27 M26 31 L24 33 L21 31"
                stroke="#64748B"
                stroke-width="1.2"
                stroke-linecap="round"
                stroke-linejoin="round"
                opacity="0.45"
            />
        </svg>
    @break

    @case('trash-full')
        <svg
            {{ $attributes->merge(['class' => $class]) }}
            viewBox="0 0 48 48"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <defs>
                <linearGradient id="binBodyFull" x1="12" y1="14" x2="36" y2="44" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#E2E8F0" stop-opacity="0.9" />
                    <stop offset="100%" stop-color="#CBD5E1" stop-opacity="0.95" />
                </linearGradient>
                <linearGradient id="binRimFull" x1="10" y1="11" x2="38" y2="15" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#F8FAFC" />
                    <stop offset="100%" stop-color="#94A3B8" />
                </linearGradient>
                <linearGradient id="paperGradWhite" x1="20" y1="5" x2="30" y2="15" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#FFFFFF" />
                    <stop offset="100%" stop-color="#E2E8F0" />
                </linearGradient>
                <linearGradient id="paperGradBlue" x1="25" y1="6" x2="35" y2="16" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#BAE6FD" />
                    <stop offset="100%" stop-color="#7DD3FC" />
                </linearGradient>
                <linearGradient id="paperGradYellow" x1="14" y1="7" x2="22" y2="17" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#FEF08A" />
                    <stop offset="100%" stop-color="#FDE047" />
                </linearGradient>
            </defs>

            {{-- Crumpled trash papers sticking out top --}}
            <path d="M16 14 L14 7 L20 9 L22 14 Z" fill="url(#paperGradYellow)" stroke="#EAB308" stroke-width="0.8" stroke-linejoin="round" />
            <path d="M21 13 L25 5 L30 8 L28 14 Z" fill="url(#paperGradWhite)" stroke="#94A3B8" stroke-width="0.8" stroke-linejoin="round" />
            <path d="M26 14 L32 7 L35 11 L31 15 Z" fill="url(#paperGradBlue)" stroke="#38BDF8" stroke-width="0.8" stroke-linejoin="round" />

            <path d="M18 11 C16 9 20 7 22 9 C24 7 28 8 27 11 C29 11 31 13 29 14 C27 16 19 15 18 11 Z" fill="#FFFFFF" stroke="#94A3B8" stroke-width="0.8" />
            <path d="M21 9 L24 12 L20 12 Z" fill="#E2E8F0" opacity="0.8" />

            {{-- Bin Body --}}
            <path
                d="M12 14.5 L15 41 C15.2 42.4 16.4 43.5 17.8 43.5 H30.2 C31.6 43.5 32.8 42.4 33 41 L36 14.5 Z"
                fill="url(#binBodyFull)"
                stroke="#94A3B8"
                stroke-width="1.2"
                stroke-linejoin="round"
            />

            {{-- Trash shadow inside semi-transparent bin --}}
            <path d="M16 21 C14 25 18 31 22 30 C25 29 28 32 30 28 C31 24 29 20 26 21 C23 22 18 19 16 21 Z" fill="#64748B" opacity="0.3" />

            {{-- Vertical ribs --}}
            <path d="M18.5 17 L20 39.5" stroke="#94A3B8" stroke-width="1.2" stroke-linecap="round" opacity="0.6" />
            <path d="M24 17.5 L24 40" stroke="#94A3B8" stroke-width="1.2" stroke-linecap="round" opacity="0.6" />
            <path d="M29.5 17 L28 39.5" stroke="#94A3B8" stroke-width="1.2" stroke-linecap="round" opacity="0.6" />

            {{-- Bin Outer Rim --}}
            <ellipse cx="24" cy="13.5" rx="14.5" ry="4" fill="none" stroke="url(#binRimFull)" stroke-width="2" />
            <ellipse cx="24" cy="13" rx="14.2" ry="3.6" fill="none" stroke="#F8FAFC" stroke-width="0.8" opacity="0.8" />

            {{-- Front folded paper hanging over rim --}}
            <path d="M22 13 L26 13 L24 17 Z" fill="#FFFFFF" stroke="#94A3B8" stroke-width="0.8" />
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