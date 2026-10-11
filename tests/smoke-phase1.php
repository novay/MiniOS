<?php

/**
 * Framework-free smoke test. Run: php tests/smoke-phase1.php
 * Integration tests against a running Laravel app/database are still required.
 */

namespace Illuminate\Support\Facades {
    class Schema
    {
        public static function hasTable(string $table): bool
        {
            return false; // Simulate a fresh install before migrations.
        }
    }
}

namespace Novay\MiniOS {
    class MiniOS
    {
        public const CORE_APPS = ['window-manager'];
    }
}

namespace {
    use Novay\MiniOS\Apps\WindowManagerApp;
    use Novay\MiniOS\Services\AppDefinitionResolver;
    use Novay\MiniOS\Support\AppRegistry;

    function config(string $key, mixed $default = null): mixed
    {
        return $key === 'minios.prefix' ? 'workspace' : $default;
    }

    function app(?string $abstract = null): mixed
    {
        static $instances = [];
        if ($abstract === null) {
            return null;
        }

        return $instances[$abstract] ??= new $abstract;
    }

    $root = dirname(__DIR__);
    require $root.'/src/Support/WindowConfig.php';
    require $root.'/src/Contracts/DesktopApp.php';
    require $root.'/src/Services/AppDefinitionResolver.php';
    require $root.'/src/Apps/WindowManagerApp.php';
    require $root.'/src/Support/AppRegistry.php';

    $checks = 0;
    $expect = static function (bool $condition, string $message) use (&$checks): void {
        if (! $condition) {
            throw new RuntimeException("FAIL: {$message}");
        }
        $checks++;
    };

    $app = new WindowManagerApp;
    $resolver = app(AppDefinitionResolver::class);
    $definition = $resolver->resolve($app);
    $expect($app->id() === 'window-manager', 'manifest app ID');
    $expect($definition['window']['width'] === 1100, 'default width');
    $expect($definition['window']['height'] === 730, 'default height');
    $expect($definition['window']['min_width'] === 800, 'minimum width');
    $expect($definition['window']['maximizable'] === true, 'maximizable');
    $expect($definition['pinned'] === false, 'not pinned by default');
    $expect($definition['layout']['menubar_enabled'] === false, 'layout default is off');
    $expect($definition['layout']['sidebar_enabled'] === false, 'sidebar default is off');
    $expect($definition['layout']['statusbar_enabled'] === false, 'statusbar default is off');

    $registry = new AppRegistry;
    $registry->register($app);
    $all = $registry->toArray();
    $expect(isset($all['window-manager']), 'registered app exists');
    $expect($all['window-manager']['entry'] === '/workspace/window-manager', 'route prefix applied');
    $expect($all['window-manager']['window']['width'] === 1100, 'window config merged');
    $expect($all['window-manager']['layout']['sidebar_enabled'] === false, 'layout exposed to desktop');

    $empty = new AppRegistry;
    $expect($empty->toArray() === [], 'empty app registry does not use undefined result');

    echo "OK: {$checks} Phase 1 smoke checks passed (no database).\n";
}
