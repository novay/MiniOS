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
