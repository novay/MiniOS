<?php

/** Framework-free checks: statusbar path isolation and per-app lifecycle cleanup. */

namespace Illuminate\Support\Facades {
    class Schema
    {
        public static array $tables = [
            'minios_app_menus', 'minios_app_menu_states', 'minios_app_sidebar_items',
            'minios_app_sidebar_states', 'minios_app_definitions',
        ];

        public static function hasTable(string $t): bool
        {
            return in_array($t, self::$tables, true);
        }
    }
    class DB
    {
        public static array $rows = [];

        public static function transaction(callable $callback): mixed
        {
            return $callback();
        }

        public static function table(string $table): object
        {
            return new class($table)
            {
                public function __construct(private string $table) {}

                public function where(string $key, string $value): object
                {
                    if ($key !== 'app_id') {
                        throw new \RuntimeException('Unsafe condition');
                    }
                    $this->value = $value;

                    return $this;
                }

                public function delete(): void
                {
                    unset(DB::$rows[$this->table][$this->value]);
                }
            };
        }
    }
}

namespace Novay\MiniOS\Services {
    class AppDefinitionResolver
    {
        public bool $forgotten = false;

        public function resolve(object $app): array
        {
            return ['layout' => ['statusbar_enabled' => $app->id() === 'todo']];
        }

        public function forget(): void
        {
            $this->forgotten = true;
        }
    }
}

namespace Novay\MiniOS\Support {
    class AppRegistry
    {
        public function get(string $id): ?object
        {
            return in_array($id, ['todo', 'notes'], true)
                ? new class($id)
                {
                    public function __construct(public string $appId) {}

                    public function id(): string
                    {
                        return $this->appId;
                    }
                }
            : null;
        }
    }
}

namespace {
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Novay\MiniOS\Services\AppDefinitionResolver;
    use Novay\MiniOS\Services\AppLifecycleService;
    use Novay\MiniOS\Services\AppStatusbarService;

    $instances = [];
    $views = ['apps.todo.customs.statusbar'];
    function app(string $class): object
    {
        global $instances;

        return $instances[$class] ??= new $class;
    }
    function view(): object
    {
        return new class
        {
            public function exists(string $name): bool
            {
                global $views;

                return in_array($name, $views, true);
            }
        };
    }
    $root = dirname(__DIR__);
    require $root.'/src/Services/AppStatusbarService.php';
    require $root.'/src/Services/AppLifecycleService.php';
    $check = function (bool $ok, string $why): void {
        if (! $ok) {
            throw new RuntimeException($why);
        }
    };
    $s = new AppStatusbarService;
    $check($s->customView('todo') === 'apps.todo.customs.statusbar', 'Custom view missing');
    $check($s->customView('notes') === null, 'Missing view must use fallback');
    $check($s->customView('../todo') === null, 'Traversal accepted');
    $check($s->customView('window.manager') === null, 'Invalid slug accepted');
    $check($s->customView('unknown') === null, 'Unregistered view accepted');
    $check($s->enabled('todo'), 'Enabled flag not used');
    $check(! $s->enabled('notes'), 'Disabled flag ignored');
    foreach (Schema::$tables as $table) {
        DB::$rows[$table] = ['todo' => ['id' => 1], 'notes' => ['id' => 2]];
    }
    (new AppLifecycleService)->forgetConfiguration('todo');
    foreach (Schema::$tables as $table) {
        $check(! isset(DB::$rows[$table]['todo']), 'Orphaned '.$table);
        $check(isset(DB::$rows[$table]['notes']), 'Deleted another app metadata');
    }
    $resolver = app(AppDefinitionResolver::class);
    $check($resolver->forgotten, 'Cache not invalidated');
    try {
        (new AppLifecycleService)->forgetConfiguration('../');
        throw new RuntimeException('Invalid app id permitted');
    } catch (InvalidArgumentException $e) { /* expected */
    }
    $shell = file_get_contents($root.'/resources/views/components/app-shell.blade.php');
    $check(str_contains($shell, ':app-id="$appId"'), 'Managed statusbar integration absent');
    $desktop = file_get_contents($root.'/resources/views/components/desktop/index.blade.php');
    $check(str_contains($desktop, 'localStorage.getItem') && str_contains($desktop, 'localStorage.setItem'), 'Preference persistence absent');
    $katalog = file_get_contents($root.'/src/Livewire/Apps/Katalog.php');
    $check(substr_count($katalog, '$this->authorizeMutation(') >= 7, 'Missing server authorization on Katalog mutations');
    $check(str_contains($katalog, 'forgetConfiguration($id)'), 'Katalog metadata cleanup absent');
    $check(! str_contains($katalog, '$this->rollbackAndCleanMigrations($realAppDir'), 'Uninstall still drops business migration');
    $check(str_contains($katalog, "'allow_web_zip_install'"), 'ZIP must be opt-in');
    echo "OK: Phase 4 statusbar isolation, fallback, per-app lifecycle, access guards and persistence.\n";
}
