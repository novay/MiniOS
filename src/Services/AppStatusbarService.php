<?php

namespace Novay\MiniOS\Services;

use Novay\MiniOS\Support\AppRegistry;

/** Resolve custom Blade templates without ever accepting a Blade view name from clients. */
class AppStatusbarService
{
    public function customView(string $appId): ?string
    {
        if (! preg_match('/^[a-z][a-z0-9-]{0,79}$/D', $appId)) {
            return null;
        }

        $app = app(AppRegistry::class)->get($appId);
        if (! $app) {
            return null;
        }

        $view = 'apps.'.$appId.'.customs.statusbar';

        return view()->exists($view) ? $view : null;
    }

    public function enabled(string $appId): bool
    {
        $app = app(AppRegistry::class)->get($appId);

        return $app && (bool) (app(AppDefinitionResolver::class)->resolve($app)['layout']['statusbar_enabled'] ?? false);
    }
}
