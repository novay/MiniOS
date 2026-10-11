<?php

namespace Novay\MiniOS\Support;

use Composer\InstalledVersions;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Services\AppDefinitionResolver;
use Novay\MiniOS\Services\AppSidebarService;

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
        $result = [];
        $resolver = app(AppDefinitionResolver::class);

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

            $definition = $resolver->resolve($app);
            $entry = $this->formatRoute($definition['entry'], $prefix);
            $ownedRoutes = (array) $definition['routes'];
            if ($definition['layout']['sidebar_enabled'] ?? false) {
                $ownedRoutes = array_merge($ownedRoutes, app(AppSidebarService::class)->routes($app));
            }
            $routes = array_values(array_unique(array_map(fn ($r) => $this->formatRoute($r, $prefix), $ownedRoutes)));

            $result[$id] = [
                'name' => $definition['name'],
                'icon' => $definition['icon'],
                'entry' => $entry,
                'routes' => $routes,
                'pinned' => $definition['pinned'],
                'component' => $definition['component'],
                'window' => $definition['window'],
                'layout' => $definition['layout'],
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
