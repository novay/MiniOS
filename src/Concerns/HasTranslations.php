<?php

namespace Novay\MiniOS\Concerns;

use Illuminate\Support\Str;
use Livewire\Attributes\On;

trait HasTranslations
{
    /**
     * In-memory cache for loaded translations across requests.
     *
     * @var array<string, array<string, string>>
     */
    protected static array $translationsCache = [];

    /**
     * Re-render component when OS locale setting is updated.
     */
    #[On('os-setting-updated')]
    public function onOsSettingUpdated(mixed ...$args): void
    {
        $payload = $args[0] ?? $args;
        $category = is_array($payload) ? ($payload['category'] ?? null) : null;
        $key = is_array($payload) ? ($payload['key'] ?? null) : null;

        if ($category === 'locale_time' && $key === 'locale') {
            static::flushTranslationsCache();
        }
    }

    /**
     * Re-render component when OS setting category is reset.
     */
    #[On('os-setting-reset')]
    public function onOsSettingReset(mixed ...$args): void
    {
        $payload = $args[0] ?? $args;
        $category = is_array($payload) ? ($payload['category'] ?? null) : null;

        if ($category === 'locale_time') {
            static::flushTranslationsCache();
        }
    }

    /**
     * Translate the given key according to active MiniOS locale.
     *
     * @param  array<string, mixed>  $replace
     */
    public function trans(string $key, array $replace = []): string
    {
        $locale = $this->getActiveLocale();
        $translations = $this->loadTranslations($locale);

        // Fallback to secondary locale (id/en) if key not found
        if (! array_key_exists($key, $translations)) {
            $candidateLocales = array_unique([
                config('minios.locale', 'id'),
                config('app.fallback_locale', 'en'),
                'id',
                'en',
            ]);

            foreach ($candidateLocales as $candidate) {
                if ($candidate === $locale) {
                    continue;
                }
                $fallbackTranslations = $this->loadTranslations($candidate);
                if (array_key_exists($key, $fallbackTranslations)) {
                    $translations = $fallbackTranslations;
                    break;
                }
            }
        }

        $line = $translations[$key] ?? $key;

        foreach ($replace as $placeholder => $value) {
            $line = str_replace(
                [':'.$placeholder, ':'.Str::upper($placeholder), ':'.Str::ucfirst($placeholder)],
                [(string) $value, Str::upper((string) $value), Str::ucfirst((string) $value)],
                $line
            );
        }

        return $line;
    }

    /**
     * Shorthand alias for trans().
     *
     * @param  array<string, mixed>  $replace
     */
    public function t(string $key, array $replace = []): string
    {
        return $this->trans($key, $replace);
    }

    /**
     * Get the active locale code (e.g. 'id' or 'en').
     */
    protected function getActiveLocale(): string
    {
        if (function_exists('os_setting')) {
            $locale = os_setting('locale_time.locale');
            if (is_string($locale) && in_array(strtolower($locale), ['id', 'en'], true)) {
                $resolved = strtolower($locale);
                if (app()->getLocale() !== $resolved) {
                    app()->setLocale($resolved);
                }

                return $resolved;
            }
        }

        return (string) config('app.locale', 'id');
    }

    /**
     * Load translations for the requested locale from disk or cache.
     *
     * @return array<string, string>
     */
    protected function loadTranslations(string $locale): array
    {
        $path = $this->getTranslationsPath();
        if (! $path) {
            return [];
        }

        $cacheKey = $path.':'.$locale;
        if (isset(static::$translationsCache[$cacheKey])) {
            return static::$translationsCache[$cacheKey];
        }

        $translations = [];

        // 1. Try JSON file: {path}/{locale}.json
        $jsonFile = $path.'/'.$locale.'.json';
        if (file_exists($jsonFile)) {
            $raw = @file_get_contents($jsonFile);
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $translations = $decoded;
                }
            }
        }

        // 2. Try PHP array file: {path}/{locale}.php
        if (empty($translations)) {
            $phpFile = $path.'/'.$locale.'.php';
            if (file_exists($phpFile)) {
                $phpData = include $phpFile;
                if (is_array($phpData)) {
                    $translations = $phpData;
                }
            }
        }

        return static::$translationsCache[$cacheKey] = $translations;
    }

    /**
     * Resolve the directory containing translation files.
     */
    protected function getTranslationsPath(): ?string
    {
        // Custom override via property if component defines it
        if (property_exists($this, 'translationsPath') && is_string($this->translationsPath)) {
            return $this->translationsPath;
        }

        $reflector = new \ReflectionClass(static::class);
        $componentDir = dirname((string) $reflector->getFileName());
        $appName = Str::kebab(class_basename(static::class));

        // 1. Check inside current folder: {ComponentDir}/lang
        if (is_dir($componentDir.'/lang')) {
            return $componentDir.'/lang';
        }

        // 2. Check in parent folder: {AppDir}/lang (common in app/MiniOS/{AppName}/Livewire/...)
        $parentDir = dirname($componentDir);
        if (is_dir($parentDir.'/lang')) {
            return $parentDir.'/lang';
        }

        // 3. Check for core built-in apps in package: resources/lang/apps/{appName}
        $packageLangDir = dirname(__DIR__, 2).'/resources/lang/apps/'.$appName;
        if (is_dir($packageLangDir)) {
            return $packageLangDir;
        }

        return null;
    }

    /**
     * Clear loaded translation cache (useful in tests).
     */
    public static function flushTranslationsCache(): void
    {
        static::$translationsCache = [];
    }
}
