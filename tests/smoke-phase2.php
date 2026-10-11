<?php

/**
 * No-framework Phase 2 test for menu defaults and the fallback rendering tree.
 * Laravel database + Livewire end-to-end tests must be performed in a host app.
 */

namespace Illuminate\Support\Facades {
    class Schema
    {
        public static function hasTable(string $table): bool
        {
            return false;
        }
    }
    class Gate
    {
        public static function allows(string $ability): bool
        {
            return $ability !== 'blocked.permission';
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
    use Novay\MiniOS\Services\AppMenuService;

    function app(?string $name = null): mixed
    {
        static $instances = [];

        return $instances[$name] ??= new $name;
    }
    $base = dirname(__DIR__);
    require $base.'/src/Support/WindowConfig.php';
    require $base.'/src/Contracts/DesktopApp.php';
    require $base.'/src/Apps/WindowManagerApp.php';
    require $base.'/src/Services/AppDefinitionResolver.php';
    require $base.'/src/Services/AppMenuService.php';

    $app = new WindowManagerApp;
    $service = new AppMenuService;
    $defs = $service->defaults($app);
    $assert = fn (bool $ok, string $message) => $ok ?: throw new RuntimeException($message);
    $assert(count($defs) === 4, 'Expected four default groups');
    $assert(array_column($defs, 'label') === ['File', 'View', 'Window', 'Help'], 'Wrong default order');
    $assert($defs[0]['children'][0]['action_type'] === 'window.close', 'Missing Close');
    $assert($defs[1]['children'] === [], 'Sidebar toggle should be conditional');
    $assert(count($defs[2]['children']) === 1, 'Statusbar toggle should be conditional');
    $assert($defs[3]['children'][0]['action_type'] === 'help.about', 'Missing About');
    $tree = $service->tree($app);
    $assert(array_column($tree, 'label') === ['File', 'Window', 'Help'], 'Empty View group should be hidden');
    $assert(! $service->isCustomized('window-manager'), 'Virtual defaults must not require DB');
    $assert(! $service->hasDatabase(), 'Fallback mode unexpectedly detected DB');
    echo "OK: Phase 2 virtual menu defaults, conditionals, and rendering fallback.\n";
}
