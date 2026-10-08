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
        $result = [];

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

            $result[$id] = [
                'name' => $app->name(),
                'icon' => $app->icon(),
                'entry' => $app->entry(),
                'routes' => $app->routes(),
                'pinned' => $app->isPinned(),
                'component' => $app->component(),
                'window' => $app->window()->toArray(),
                'packages' => $requiredPackages,
                'missing_packages' => $missingPackages,
            ];
        }

        return $result;
    }
}
