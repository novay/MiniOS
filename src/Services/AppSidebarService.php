<?php

namespace Novay\MiniOS\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\MiniOS;
use Novay\MiniOS\Models\AppSidebarItem;
use Novay\MiniOS\Models\AppSidebarState;
use Novay\MiniOS\Support\AppRegistry;

/** Server-authoritative application navigation (no user-authored PHP or JS in DB). */
class AppSidebarService
{
    public function hasDatabase(): bool
    {
        try {
            return Schema::hasTable('minios_app_sidebar_items') && Schema::hasTable('minios_app_sidebar_states');
        } catch (\Throwable) {
            return false;
        }
    }

    public function isCustomized(string $appId): bool
    {
        return $this->hasDatabase() && AppSidebarState::query()->whereKey($appId)->exists();
    }

    public function defaults(DesktopApp $app): array
    {
        return [[
            'id' => null,
            'slug' => 'home', 'type' => 'default', 'label' => 'Home',
            'description' => null, 'icon' => 'home', 'icon_color' => 'text-neutral-500',
            'badge' => null, 'permissions' => [],
            'route_prefix' => app(AppDefinitionResolver::class)->resolve($app)['entry'],
            'options' => [], 'enabled' => true, 'sort_order' => 0,
        ]];
    }

    /** @return array<int,array<string,mixed>> */
    public function items(DesktopApp $app, bool $filter = true): array
    {
        $items = $this->isCustomized($app->id())
            ? AppSidebarItem::query()->where('app_id', $app->id())->orderBy('sort_order')->orderBy('id')->get()
                ->map(fn (AppSidebarItem $item) => $item->only([
                    'id', 'slug', 'type', 'label', 'description', 'icon', 'icon_color', 'badge',
                    'permissions', 'route_prefix', 'options', 'enabled', 'sort_order',
                ]))->all()
            : $this->defaults($app);

        if (! $filter) {
            return $items;
        }

        return array_values(array_filter($items, static function (array $item) {
            if (! $item['enabled']) {
                return false;
            }
            foreach ($item['permissions'] ?? [] as $ability) {
                if (! is_string($ability) || ! Gate::allows($ability)) {
                    return false;
                }
            }

            return true;
        }));
    }

    public function startEditing(DesktopApp $app): void
    {
        if (! $this->hasDatabase()) {
            throw new \RuntimeException('Jalankan php artisan migrate sebelum mengedit sidebar.');
        }
        DB::transaction(function () use ($app) {
            if (AppSidebarState::query()->whereKey($app->id())->lockForUpdate()->exists()) {
                return;
            }
            AppSidebarState::query()->create(['app_id' => $app->id()]);
            foreach ($this->defaults($app) as $item) {
                unset($item['id']);
                AppSidebarItem::query()->create(['app_id' => $app->id()] + $item);
            }
        });
    }

    public function reset(DesktopApp $app): void
    {
        DB::transaction(function () use ($app) {
            AppSidebarItem::query()->where('app_id', $app->id())->delete();
            AppSidebarState::query()->whereKey($app->id())->delete();
        });
    }

    /** Only explicit enabled sidebar entries extend the virtual router. */
    public function routes(DesktopApp $app): array
    {
        $routes = [];
        foreach ($this->items($app, false) as $item) {
            if (! $item['enabled']) {
                continue;
            }
            $routes[] = $item['route_prefix'];
        }

        return array_values(array_unique($routes));
    }

    /** Route without MiniOS prefix; rejects unowned and unauthorized destinations. */
    public function relativePath(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH);
        if (! is_string($path) || ! preg_match('~^/(?!/)[a-zA-Z0-9/_-]*$~D', $path) || str_contains($path, '..')) {
            abort(404);
        }
        $path = rtrim($path, '/') ?: '/';
        $prefix = MiniOS::prefix();
        if ($prefix !== '') {
            if ($path === '/'.$prefix) {
                return '/';
            }
            abort_unless(str_starts_with($path, '/'.$prefix.'/'), 404);
            $path = substr($path, strlen($prefix) + 1);
        }

        return $path;
    }

    public function page(DesktopApp $app, string $path): ?array
    {
        $path = $this->relativePath($path);
        foreach ($this->items($app) as $item) {
            $base = rtrim($item['route_prefix'], '/') ?: '/';
            if ($item['type'] === 'default' && $path === $base) {
                return ['type' => 'default', 'component' => app(AppDefinitionResolver::class)->resolve($app)['component'], 'recordId' => null];
            }
            if ($item['type'] !== 'crud') {
                continue;
            }
            $suffix = $path === $base ? '' : (str_starts_with($path, $base.'/') ? substr($path, strlen($base)) : null);
            if ($suffix === null) {
                continue;
            }
            $page = match (true) {
                $suffix === '' => 'Index',
                $suffix === '/create' => 'Create',
                (bool) preg_match('~^/([A-Za-z0-9_-]{1,100})/edit$~D', $suffix) => 'Edit',
                (bool) preg_match('~^/([A-Za-z0-9_-]{1,100})$~D', $suffix) => 'Show',
                default => null,
            };
            if ($page === null) {
                continue;
            }
            preg_match('~^/([A-Za-z0-9_-]{1,100})(?:/edit)?$~D', $suffix, $m);
            $recordId = $m[1] ?? null;
            $namespace = substr($app::class, 0, (int) strrpos($app::class, '\\'));
            $class = $namespace.'\\Livewire\\'.str_replace(' ', '', ucwords(str_replace('-', ' ', $item['slug']))).'\\'.$page;

            return ['type' => 'crud', 'component' => $class, 'recordId' => $recordId, 'page' => $page];
        }

        return null;
    }

    /**
     * Check sidebar route ambiguity, not mere URL ancestry.
     *
     * A Default parent (/todo) can safely coexist with a CRUD child (/todo/tasks).
     * A Default child of CRUD (/todo/tasks/archive), however, is ambiguous with
     * the CRUD Show route (/todo/tasks/{recordId}). Nested CRUD prefixes are also
     * ambiguous and therefore not allowed.
     *
     * @param  array{type:string,route_prefix:string}  $existing
     */
    public function conflictsWithSidebarItem(string $path, string $type, array $existing): bool
    {
        $path = rtrim($path, '/') ?: '/';
        $existingPath = rtrim($existing['route_prefix'], '/') ?: '/';
        $existingType = $existing['type'];

        // Exact duplicates are always invalid regardless of sidebar item types.
        if ($path === $existingPath) {
            return true;
        }

        // Other Default items have no catch-all route and cannot shadow children.
        if ($type === 'default' && $existingType === 'default') {
            return false;
        }

        // Any route beneath a CRUD prefix can be captured as /{recordId}.
        if ($existingType === 'crud' && str_starts_with($path, $existingPath.'/')) {
            return true;
        }
        if ($type === 'crud' && str_starts_with($existingPath, $path.'/')) {
            return true;
        }

        return false;
    }

    public function isReservedRoute(DesktopApp $app, string $path, ?int $exceptId = null): bool
    {
        foreach (app(AppRegistry::class)->all() as $otherId => $other) {
            if ($otherId === $app->id()) {
                foreach ($this->items($other, false) as $item) {
                    if ($item['id'] !== $exceptId && $item['route_prefix'] === $path) {
                        return true;
                    }
                }

                continue;
            }
            $definition = app(AppDefinitionResolver::class)->resolve($other);
            foreach (array_merge($definition['routes'], $this->routes($other)) as $otherRoute) {
                if ($path === $otherRoute || str_starts_with($path.'/', rtrim($otherRoute, '/').'/')) {
                    return true;
                }
            }
        }

        return false;
    }
}
