<?php

namespace Novay\MiniOS\Services;

use Illuminate\Support\Facades\Schema;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\MiniOS;
use Novay\MiniOS\Models\AppDefinition;

/**
 * Resolves immutable PHP manifest defaults plus optional DB-managed overrides.
 * No DesktopApp interface changes and no per-user setting leakage.
 */
class AppDefinitionResolver
{
    /** @var array<string, array<string, mixed>>|null */
    private ?array $stored = null;

    public function defaults(DesktopApp $app): array
    {
        return [
            'name' => $app->name(),
            'icon' => $app->icon(),
            'entry' => $app->entry(),
            'routes' => $app->routes(),
            'pinned' => $app->isPinned(),
            'component' => $app->component(),
            'window' => $app->window()->toArray(),
            'layout' => [
                'shell_mode' => in_array($app->id(), MiniOS::CORE_APPS, true) ? 'embedded' : 'managed',
                'menubar_enabled' => false,
                'sidebar_enabled' => false,
                'statusbar_enabled' => false,
            ],
        ];
    }

    public function resolve(DesktopApp $app): array
    {
        $defaults = $this->defaults($app);
        $overrides = $this->stored()[$app->id()] ?? [];

        // Allowlist keys so that arbitrary JSON never becomes desktop configuration.
        foreach (['name', 'icon', 'entry', 'routes', 'pinned', 'component'] as $key) {
            if (array_key_exists($key, $overrides)) {
                $defaults[$key] = $overrides[$key];
            }
        }

        foreach (['window', 'layout'] as $section) {
            if (isset($overrides[$section]) && is_array($overrides[$section])) {
                $defaults[$section] = array_replace(
                    $defaults[$section],
                    array_intersect_key($overrides[$section], $defaults[$section] + [
                        // Nullable max dimensions may not exist in WindowConfig::toArray().
                        'max_width' => null,
                        'max_height' => null,
                    ])
                );
            }
        }

        // Maintain the same null-key behaviour as WindowConfig::toArray().
        $defaults['window'] = array_filter($defaults['window'], static fn ($value) => $value !== null);

        return $defaults;
    }

    /**
     * Store only values differing from the PHP manifest. Resetting to defaults
     * therefore requires no forced regeneration of PHP source files.
     */
    public function save(DesktopApp $app, array $definition, ?int $actorId = null): array
    {
        if (! Schema::hasTable('minios_app_definitions')) {
            throw new \RuntimeException('Jalankan php artisan migrate sebelum menyimpan konfigurasi Window Manager.');
        }

        $base = $this->defaults($app);
        $changes = [];

        foreach (['name', 'icon', 'entry', 'routes', 'pinned', 'component'] as $key) {
            if (array_key_exists($key, $definition) && $definition[$key] !== $base[$key]) {
                $changes[$key] = $definition[$key];
            }
        }

        foreach (['window', 'layout'] as $section) {
            foreach ($definition[$section] ?? [] as $key => $value) {
                if (array_key_exists($key, $base[$section]) || ($section === 'window' && in_array($key, ['max_width', 'max_height'], true))) {
                    if ($value !== ($base[$section][$key] ?? null)) {
                        $changes[$section][$key] = $value;
                    }
                }
            }
        }

        if ($changes === []) {
            AppDefinition::query()->whereKey($app->id())->delete();
        } else {
            $model = AppDefinition::query()->firstOrNew(['app_id' => $app->id()]);
            if (! $model->exists) {
                $model->created_by = $actorId;
            }
            $model->overrides = $changes;
            $model->updated_by = $actorId;
            $model->save();
        }

        $this->forget();

        return $changes;
    }

    public function reset(DesktopApp $app): void
    {
        if (! Schema::hasTable('minios_app_definitions')) {
            throw new \RuntimeException('Jalankan php artisan migrate terlebih dahulu.');
        }

        AppDefinition::query()->whereKey($app->id())->delete();
        $this->forget();
    }

    public function hasDatabase(): bool
    {
        try {
            return Schema::hasTable('minios_app_definitions');
        } catch (\Throwable) {
            return false;
        }
    }

    public function hasOverride(string $appId): bool
    {
        return array_key_exists($appId, $this->stored());
    }

    public function forget(): void
    {
        $this->stored = null;
    }

    /** @return array<string, array<string, mixed>> */
    private function stored(): array
    {
        if ($this->stored !== null) {
            return $this->stored;
        }

        $this->stored = [];
        // Migrations, Composer scripts and initial installs must work without a DB.
        try {
            if (Schema::hasTable('minios_app_definitions')) {
                foreach (AppDefinition::query()->get(['app_id', 'overrides']) as $row) {
                    if (is_array($row->overrides)) {
                        $this->stored[$row->app_id] = $row->overrides;
                    }
                }
            }
        } catch (\Throwable) {
            // Fall back to manifest data when the DB is not yet ready.
        }

        return $this->stored;
    }
}
