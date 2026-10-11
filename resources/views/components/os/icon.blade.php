@props([
    'name',
    'variant' => 'outline',
    'filled' => false,
    'strokeWidth' => null,
    'desktopApp' => false,
])

@php
    $cleanName = (string) $name;

    // Check if user explicitly requests a native MiniOS desktop application icon
    $isAppIcon = $desktopApp || str_starts_with($cleanName, 'app:');
    $appName = str_starts_with($cleanName, 'app:') ? substr($cleanName, 4) : $cleanName;

    // Check if image URL or static file path
    $isImage = str_starts_with($cleanName, 'http://')
        || str_starts_with($cleanName, 'https://')
        || str_starts_with($cleanName, '/')
        || str_ends_with($cleanName, '.png')
        || str_ends_with($cleanName, '.webp')
        || str_ends_with($cleanName, '.svg');

    if (! $isImage && ! $isAppIcon) {
        // Strip prefixes if user passed "tabler:xxx" or "tabler-xxx" or "os:xxx"
        if (str_starts_with($cleanName, 'tabler:')) {
            $cleanName = substr($cleanName, 7);
        } elseif (str_starts_with($cleanName, 'tabler-')) {
            $cleanName = substr($cleanName, 7);
        } elseif (str_starts_with($cleanName, 'os:')) {
            $cleanName = substr($cleanName, 3);
        }

        // Common alias normalizations (Heroicons / Lucide -> Tabler)
        $aliases = [
            'check-circle' => 'circle-check',
            'information-circle' => 'info-circle',
            'exclamation-circle' => 'alert-circle',
            'exclamation-triangle' => 'alert-triangle',
            'x-mark' => 'x',
            'magnifying-glass' => 'search',
            'cog-6-tooth' => 'settings',
            'cog-8-tooth' => 'settings',
            'cog' => 'settings',
            'bars-3' => 'menu',
            'chevron-up-down' => 'selector',
            'arrow-path' => 'refresh',
            'arrow-down-tray' => 'download',
            'arrow-up-tray' => 'upload',
            'pencil-square' => 'edit',
            'trash-bin' => 'trash',
        ];

        if (isset($aliases[$cleanName])) {
            $cleanName = $aliases[$cleanName];
        }

        // Handle filled variant
        $isFilled = $filled || $variant === 'filled' || str_ends_with($cleanName, '-filled');
        if ($isFilled && ! str_ends_with($cleanName, '-filled')) {
            $filledCandidate = "tabler-{$cleanName}-filled";
            try {
                if (svg($filledCandidate)->name() === "{$cleanName}-filled") {
                    $cleanName = "{$cleanName}-filled";
                }
            } catch (\Throwable) {
                // Keep base cleanName
            }
        }

        $tablerIcon = "tabler-{$cleanName}";
    }
@endphp

@if ($isImage)
    <img src="{{ $cleanName }}" alt="" {{ $attributes->merge(['class' => 'size-5 inline-block']) }} />
@elseif ($isAppIcon)
    <x-minios.icon :name="$appName" {{ $attributes }} />
@else
    @php
        $classes = $attributes->get('class') ?? 'size-5';
        $otherAttributes = $attributes->except('class')->all();

        // Stroke width customization
        $customStrokeWidth = $strokeWidth ?? $attributes->get('stroke-width') ?? $attributes->get('strokeWidth');
        unset($otherAttributes['stroke-width'], $otherAttributes['strokeWidth']);

        try {
            $svgOutput = svg($tablerIcon, $classes, $otherAttributes)->toHtml();
        } catch (\Throwable $e) {
            $svgOutput = svg('tabler-help', $classes, $otherAttributes)->toHtml();
        }

        if ($customStrokeWidth !== null) {
            $svgOutput = preg_replace('/stroke-width="[^"]*"/', 'stroke-width="'.$customStrokeWidth.'"', $svgOutput, 1);
        }
    @endphp
    {!! $svgOutput !!}
@endif
