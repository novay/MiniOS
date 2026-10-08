<?php

namespace Novay\MiniOS\Services;

use Illuminate\Support\Facades\Cache;
use Novay\MiniOS\Models\Setting;

class SettingService
{
    /**
     * Cache TTL in seconds (1 day).
     */
    protected int $cacheTtl = 86400;

    /**
     * Get a setting value by dot-notation key (e.g. 'appearance.theme').
     */
    public function get(string $key, mixed $default = null, ?int $userId = null): mixed
    {
        $resolvedUserId = $this->resolveUserId($userId);
        [$category, $settingKey] = $this->parseKey($key);

        $categorySettings = $this->getCategory($category, $resolvedUserId);

        return $categorySettings[$settingKey] ?? $default ?? config("minios.settings.{$category}.{$settingKey}", config("desktop.settings.{$category}.{$settingKey}"));
    }

    /**
     * Set a setting value by dot-notation key.
     */
    public function set(string $key, mixed $value, ?int $userId = null): void
    {
        $resolvedUserId = $this->resolveUserId($userId);
        [$category, $settingKey] = $this->parseKey($key);

        Setting::updateOrCreate(
            [
                'user_id' => $resolvedUserId,
                'category' => $category,
                'key' => $settingKey,
            ],
            [
                'value' => $value,
            ]
        );

        $this->clearCache($category, $resolvedUserId);
    }

    /**
     * Get all settings for a specific category, merged with system defaults.
     *
     * @return array<string, mixed>
     */
    public function getCategory(string $category, ?int $userId = null): array
    {
        $resolvedUserId = $this->resolveUserId($userId);
        $cacheKey = $this->getCacheKey($category, $resolvedUserId);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($category, $resolvedUserId) {
            $defaults = config("minios.settings.{$category}", config("desktop.settings.{$category}", []));

            $userSettings = Setting::query()
                ->where('user_id', $resolvedUserId)
                ->where('category', $category)
                ->pluck('value', 'key')
                ->toArray();

            return array_merge($defaults, $userSettings);
        });
    }

    /**
     * Update multiple settings for a specific category.
     *
     * @param  array<string, mixed>  $values
     */
    public function setCategory(string $category, array $values, ?int $userId = null): void
    {
        $resolvedUserId = $this->resolveUserId($userId);

        foreach ($values as $settingKey => $value) {
            Setting::updateOrCreate(
                [
                    'user_id' => $resolvedUserId,
                    'category' => $category,
                    'key' => $settingKey,
                ],
                [
                    'value' => $value,
                ]
            );
        }

        $this->clearCache($category, $resolvedUserId);
    }

    /**
     * Reset a category back to its system defaults for the user.
     */
    public function resetCategory(string $category, ?int $userId = null): void
    {
        $resolvedUserId = $this->resolveUserId($userId);

        Setting::query()
            ->where('user_id', $resolvedUserId)
            ->where('category', $category)
            ->delete();

        $this->clearCache($category, $resolvedUserId);
    }

    /**
     * Get all settings across all categories merged with defaults.
     *
     * @return array<string, array<string, mixed>>
     */
    public function all(?int $userId = null): array
    {
        $categories = array_keys(config('minios.settings', config('desktop.settings', [])));
        $resolvedUserId = $this->resolveUserId($userId);
        $result = [];

        foreach ($categories as $category) {
            $result[$category] = $this->getCategory($category, $resolvedUserId);
        }

        return $result;
    }

    /**
     * Clear cache for a specific category and user.
     */
    public function clearCache(string $category, ?int $userId = null): void
    {
        $resolvedUserId = $this->resolveUserId($userId);
        Cache::forget($this->getCacheKey($category, $resolvedUserId));
    }

    /**
     * Parse key into category and setting key.
     *
     * @return array{0: string, 1: string}
     */
    protected function parseKey(string $key): array
    {
        $parts = explode('.', $key, 2);

        if (count($parts) < 2) {
            return ['general', $parts[0]];
        }

        return [$parts[0], $parts[1]];
    }

    /**
     * Generate cache key for user and category.
     */
    protected function getCacheKey(string $category, ?int $userId = null): string
    {
        $userKey = $userId ?? 'guest';

        return "os_setting:{$userKey}:{$category}";
    }

    /**
     * Resolve user ID, falling back to authenticated user.
     */
    protected function resolveUserId(?int $userId = null): ?int
    {
        return $userId ?? auth()->id();
    }
}
