<div {{ $attributes->class([
    'flex items-center isolate -space-x-2.5',
    '**:data-[slot=avatar]:ring-2 **:data-[slot=avatar]:ring-white **:dark:data-[slot=avatar]:ring-[#202020]',
    '**:data-[slot=avatar]:transition-all **:data-[slot=avatar]:duration-150 **:data-[slot=avatar]:hover:z-30 **:data-[slot=avatar]:hover:scale-110 **:data-[slot=avatar]:hover:shadow-md',
]) }} data-os-avatar-group="true">
    {{ $slot }}
</div>
