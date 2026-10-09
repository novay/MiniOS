@props([
    'label' => '',
    'id' => null,
])

@php
    $menuId = $id ?? 'menu-'.\Illuminate\Support\Str::slug($label);
@endphp

<div class="relative">
    <button
        type="button"
        @click.stop="toggleMenu('{{ $menuId }}')"
        @mouseenter="hoverMenu('{{ $menuId }}')"
        :class="activeMenu === '{{ $menuId }}' ? 'bg-black/10 dark:bg-white/15 text-neutral-900 dark:text-white' : 'text-neutral-600 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/10'"
        class="inline-flex items-center px-2 py-1 text-xs rounded transition-colors focus:outline-none"
    >
        <span>{{ $label }}</span>
    </button>

    <div
        x-cloak
        x-show="activeMenu === '{{ $menuId }}'"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute left-0 top-full mt-1 z-50 min-w-[210px] rounded-lg border border-black/10 dark:border-white/10 bg-white/95 dark:bg-[#1e1e1e]/95 p-1 text-xs text-neutral-800 dark:text-neutral-200 shadow-2xl backdrop-blur-2xl"
        @click.stop
    >
        {{ $slot }}
    </div>
</div>
