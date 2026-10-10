<?php

namespace Novay\MiniOS\Support;

use Composer\InstalledVersions;
use Novay\MiniOS\Contracts\DesktopApp;

class AppRegistry
{
    /**
     * @var array<string, DesktopApp>
     */
    protected array $apps = [];

    /**
     * Register an application instance or class string.
     */
    public function register(DesktopApp|string $app): self
    {
        if (is_string($app)) {
            $app = app($app);
        }

        $this->apps[$app->id()] = $app;

        return $this;
    }

    /**
     * Get all registered applications.
     *
     * @return array<string, DesktopApp>
     */
    public function all(): array
    {
        return $this->apps;
    }

    /**
     * Find an application by ID.
     */
    public function get(string $id): ?DesktopApp
    {
        return $this->apps[$id] ?? null;
    }

    /**
     * Unregister an application by ID.
     */
    public function unregister(string $id): self
    {
        unset($this->apps[$id]);

        return $this;
    }

    /**
     * Convert registered apps to desktop array representation.
     *
     * @return array<string, array<string, mixed>>
     */
    public function toArray(): array
    {
        $prefix = trim(config('minios.prefix', ''), '/');

        foreach ($this->apps as $id => $app) {
            $requiredPackages = [];
            if (method_exists($app, 'packages')) {
                $requiredPackages = (array) $app->packages();
            } elseif (method_exists($app, 'dependencies')) {
                $requiredPackages = (array) $app->dependencies();
            }

            $missingPackages = [];
            foreach ($requiredPackages as $pkg) {
                if (is_string($pkg) && ! empty($pkg) && ! InstalledVersions::isInstalled($pkg)) {
                    $missingPackages[] = $pkg;
                }
            }

            $entry = $this->formatRoute($app->entry(), $prefix);
            $routes = array_map(fn ($r) => $this->formatRoute($r, $prefix), (array) $app->routes());

            $result[$id] = [
                'name' => $app->name(),
                'icon' => $app->icon(),
                'entry' => $entry,
                'routes' => $routes,
                'pinned' => $app->isPinned(),
                'component' => $app->component(),
                'window' => $app->window()->toArray(),
                'packages' => $requiredPackages,
                'missing_packages' => $missingPackages,
            ];
        }

        return $result;
    }

    /**
     * Format route with configured prefix.
     */
    protected function formatRoute(string $route, string $prefix): string
    {
        $cleanRoute = '/'.ltrim($route, '/');
        if ($prefix === '') {
            return $cleanRoute;
        }

        $prefixPath = "/{$prefix}";

        if (str_starts_with($cleanRoute, "{$prefixPath}/") || $cleanRoute === $prefixPath) {
            return $cleanRoute;
        }

        if (str_starts_with($cleanRoute, '/desktop/')) {
            $cleanRoute = substr($cleanRoute, 8);
        }

        return rtrim($prefixPath, '/').'/'.ltrim($cleanRoute, '/');
    }
}
