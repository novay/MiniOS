<?php

use Novay\MiniOS\Services\SettingService;

if (! function_exists('os_setting')) {
    /**
     * Get or set MiniOS setting.
     */
    function os_setting(?string $key = null, mixed $default = null, ?int $userId = null): mixed
    {
        $service = app(SettingService::class);

        if (is_null($key)) {
            return $service;
        }

        return $service->get($key, $default, $userId);
    }
}

if (! function_exists('os_path')) {
    /**
     * Get MiniOS path with configured prefix.
     */
    function os_path(?string $path = null): string
    {
        $prefix = trim(config('minios.prefix', ''), '/');
        $cleanPath = $path !== null ? trim($path, '/') : '';

        if ($prefix === '') {
            return $cleanPath === '' ? '/' : "/{$cleanPath}";
        }

        return $cleanPath === '' ? "/{$prefix}" : "/{$prefix}/{$cleanPath}";
    }
}

if (! function_exists('os_url')) {
    /**
     * Get MiniOS full URL with configured prefix.
     */
    function os_url(?string $path = null): string
    {
        return url(os_path($path));
    }
}
