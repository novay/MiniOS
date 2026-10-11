<?php

/** Framework-free smoke test for Phase 3 virtual navigation and safe scaffold generation. */

namespace Illuminate\Support\Facades {
    class Schema
    {
        public static function hasTable(string $name): bool
        {
            return false;
        }
    }
    class Gate
    {
        public static function allows(string $ability): bool
        {
            return $ability !== 'deny';
        }
    }
    class File
    {
        public static function makeDirectory(string $path, int $mode = 0755, bool $recursive = false): bool
        {
            return mkdir($path, $mode, $recursive);
        }
    }
}

namespace Illuminate\Support {
    class Str
    {
        public static function studly(string $value): string
        {
            return str_replace(' ', '', ucwords(str_replace('-', ' ', $value)));
        }

        public static function kebab(string $value): string
        {
            return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $value));
        }
    }
}

namespace Novay\MiniOS {
    class MiniOS
    {
        public const CORE_APPS = ['window-manager'];

        public static function prefix(): string
        {
            return 'desktop';
        }

        public static function path(?string $path = null): string
        {
            return '/desktop'.($path ? '/'.trim($path, '/') : '');
        }
    }
}

namespace Novay\MiniOS\Models {
    class AppSidebarItem
    {
        public string $app_id;

        public string $slug;

        public string $type;

        public string $label;

        public function __construct(array $data)
        {
            foreach ($data as $k => $v) {
                $this->$k = $v;
            }
        }
    }
}

namespace {
    use App\MiniOS\Todo\TodoApp;
    use Novay\MiniOS\Contracts\DesktopApp;
    use Novay\MiniOS\Models\AppSidebarItem;
    use Novay\MiniOS\Services\AppSidebarService;
    use Novay\MiniOS\Services\CrudScaffoldGenerator;

    function app(?string $name = null): mixed
    {
        static $objects = [];
        if ($name === null) {
            return new class
            {
                public function environment(string $value): bool
                {
                    return true;
                }
            };
        }

        return $objects[$name] ??= new $name;
    }
    function app_path(string $path = ''): string
    {
        return $GLOBALS['phase3tmp'].'/app/'.$path;
    }
    function resource_path(string $path = ''): string
    {
        return $GLOBALS['phase3tmp'].'/resources/'.$path;
    }
    function config(string $name, mixed $default = null): mixed
    {
        return $name === 'minios.allow_web_scaffolding' ? true : $default;
    }
    function abort(int $status): never
    {
        throw new RuntimeException('Abort '.$status);
    }
    function abort_unless(bool $condition, int $status): void
    {
        if (! $condition) {
            abort($status);
        }
    }
    $root = dirname(__DIR__);
    $GLOBALS['phase3tmp'] = sys_get_temp_dir().'/minios-phase3-'.bin2hex(random_bytes(5));
    mkdir($GLOBALS['phase3tmp'].'/resources/views', 0755, true);
    $dir = app_path('MiniOS/Todo');
    mkdir($dir, 0755, true);
    $file = $dir.'/TodoApp.php';
    file_put_contents($file, <<<'TEMPLATE'
<?php
namespace App\MiniOS\Todo;
class TodoApp implements \Novay\MiniOS\Contracts\DesktopApp {
    public function id(): string { return 'todo'; }
    public function name(): string { return 'Todo'; }
    public function icon(): string { return 'folder'; }
    public function entry(): string { return '/todo'; }
    public function routes(): array { return ['/todo']; }
    public function isPinned(): bool { return false; }
    public function component(): ?string { return 'App\\MiniOS\\Todo\\Livewire\\Todo'; }
    public function window(): \Novay\MiniOS\Support\WindowConfig { return \Novay\MiniOS\Support\WindowConfig::make(); }
}
TEMPLATE);
    require $root.'/src/Support/WindowConfig.php';
    require $root.'/src/Contracts/DesktopApp.php';
    require $file;
    require $root.'/src/Services/AppDefinitionResolver.php';
    require $root.'/src/Services/AppSidebarService.php';
    require $root.'/src/Services/CrudScaffoldGenerator.php';
    $check = fn (bool $condition, string $msg) => $condition ?: throw new RuntimeException($msg);
    $todo = new TodoApp;
    $sidebar = new AppSidebarService;
    $default = $sidebar->items($todo);
    $check(count($default) === 1 && $default[0]['slug'] === 'home', 'Default Home missing');
    $check($default[0]['route_prefix'] === '/todo', 'Home route mismatch');
    $check($sidebar->relativePath('/desktop/todo') === '/todo', 'URL prefix not stripped');
    $check($sidebar->page($todo, '/desktop/todo')['type'] === 'default', 'Default page missing');
    $check($sidebar->page($todo, '/desktop/todo/tasks') === null, 'Unknown route should be rejected');
    try {
        $sidebar->relativePath('//evil');
        throw new RuntimeException('Invalid URL accepted');
    } catch (RuntimeException $e) {
        $check($e->getMessage() === 'Abort 404', 'Unexpected invalid URL result');
    }
    $item = new AppSidebarItem(['app_id' => 'todo', 'slug' => 'tasks', 'type' => 'crud', 'label' => 'Tasks', 'route_prefix' => '/todo/tasks']);
    $withCrud = new class extends AppSidebarService
    {
        public function items(DesktopApp $app, bool $filter = true): array
        {
            return array_merge(parent::items($app, $filter), [[
                'id' => 7, 'slug' => 'tasks', 'type' => 'crud', 'label' => 'Tasks',
                'route_prefix' => '/todo/tasks', 'permissions' => [], 'enabled' => true,
            ]]);
        }
    };
    $check($withCrud->page($todo, '/desktop/todo/tasks')['page'] === 'Index', 'Index route failed');
    $check($withCrud->page($todo, '/desktop/todo/tasks/create')['page'] === 'Create', 'Create route failed');
    $check($withCrud->page($todo, '/desktop/todo/tasks/42/edit')['recordId'] === '42', 'Edit route failed');
    $check($withCrud->page($todo, '/desktop/todo/tasks/abc-4')['page'] === 'Show', 'Show route failed');
    $check($withCrud->page($todo, '/desktop/todo/tasks/abc-4/nested') === null, 'Invalid suffix should fail');
    $gen = new CrudScaffoldGenerator;
    $paths = $gen->paths($todo, $item);
    $check(count($paths) === 4, 'Missing CRUD page');
    $check(str_ends_with($paths['Index']['blade'], 'apps/todo/tasks/index.blade.php'), 'Incorrect view path');
    $files = $gen->generate($todo, $item);
    $check(count($files) === 8, 'Expected eight files');
    $check(str_contains(file_get_contents($paths['Index']['blade']), '/desktop\\/todo\\/tasks\\/create'), 'Generated Create navigation incorrect');
    foreach ($files as $generated) {
        $check(is_file($generated), 'File absent: '.$generated);
        if (str_ends_with($generated, '.php')) {
            exec('php -l '.escapeshellarg($generated).' 2>&1', $output, $exit);
            $check($exit === 0, 'Invalid generated PHP: '.$generated);
        }
    }
    $original = file_get_contents($paths['Index']['php']);
    try {
        $gen->generate($todo, $item);
        throw new RuntimeException('Overwrite permitted');
    } catch (RuntimeException $e) {
        $check(str_contains($e->getMessage(), 'file sudah ada'), 'Wrong overwrite protection');
    }
    $check(file_get_contents($paths['Index']['php']) === $original, 'Overwrite modified a file');
    echo "OK: Phase 3 default routing, URL validation, 8 generated files, PHP lint, and overwrite guard.\n";
}
