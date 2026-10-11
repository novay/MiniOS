<div {{ $attributes->class([
    'inline-flex items-center rounded-md isolate shadow-2xs',
    '[&>button:not(:first-child)]:-ms-px [&>button:not(:first-child)]:rounded-s-none [&>button:not(:last-child)]:rounded-e-none',
    '[&>a:not(:first-child)]:-ms-px [&>a:not(:first-child)]:rounded-s-none [&>a:not(:last-child)]:rounded-e-none',
    '[&>*:focus-visible]:z-10',
]) }} data-os-button-group="true">
    {{ $slot }}
</div>
