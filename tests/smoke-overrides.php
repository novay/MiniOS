<?php

/** Framework-free in-memory persistence contract test (not a Laravel integration test). */

namespace Illuminate\Support\Facades {
    class Schema
    {
        public static function hasTable(string $name): bool
        {
            return $name === 'minios_app_definitions';
        }
    }
}

namespace Novay\MiniOS {
    class MiniOS
    {
        public const CORE_APPS = ['window-manager'];
    }
}

namespace Novay\MiniOS\Models {
    class AppDefinition
    {
        public static array $rows = [];

        public bool $exists = false;

        public string $app_id;

        public array $overrides = [];

        public ?int $created_by = null;

        public ?int $updated_by = null;

        public static function query(): FakeQuery
        {
            return new FakeQuery;
        }

        public function save(): void
        {
            self::$rows[$this->app_id] = clone $this;
            $this->exists = true;
        }
    }

    class FakeQuery
    {
        private ?string $key = null;

        public function get(array $columns = []): array
        {
            return array_values(AppDefinition::$rows);
        }

        public function whereKey(string $id): self
        {
            $this->key = $id;

            return $this;
        }

        public function delete(): void
        {
            unset(AppDefinition::$rows[$this->key]);
        }

        public function firstOrNew(array $where): AppDefinition
        {
            if (isset(AppDefinition::$rows[$where['app_id']])) {
                return clone AppDefinition::$rows[$where['app_id']];
            }
            $model = new AppDefinition;
            $model->app_id = $where['app_id'];

            return $model;
        }
    }
}

namespace {
    use Novay\MiniOS\Apps\WindowManagerApp;
    use Novay\MiniOS\Models\AppDefinition;
    use Novay\MiniOS\Services\AppDefinitionResolver;

    $root = dirname(__DIR__);
    require $root.'/src/Support/WindowConfig.php';
    require $root.'/src/Contracts/DesktopApp.php';
    require $root.'/src/Apps/WindowManagerApp.php';
    require $root.'/src/Services/AppDefinitionResolver.php';

    $app = new WindowManagerApp;
    $resolver = new AppDefinitionResolver;
    $base = $resolver->resolve($app);
    assert($base['window']['width'] === 1100);

    $edited = $base;
    $edited['name'] = 'Window Manager Custom';
    $edited['window']['width'] = 1240;
    $edited['layout']['sidebar_enabled'] = true;
    $diff = $resolver->save($app, $edited, 7);

    if ($diff !== [
        'name' => 'Window Manager Custom',
        'window' => ['width' => 1240],
        'layout' => ['sidebar_enabled' => true],
    ]) {
        throw new RuntimeException('Unexpected manifest diff: '.json_encode($diff));
    }
    $stored = AppDefinition::$rows[$app->id()] ?? null;
    if (! $stored || $stored->updated_by !== 7) {
        throw new RuntimeException('Override was not persisted with audit user ID.');
    }
    $resolved = $resolver->resolve($app);
    if ($resolved['name'] !== 'Window Manager Custom' || $resolved['window']['height'] !== 730 || ! $resolved['layout']['sidebar_enabled']) {
        throw new RuntimeException('Stored overrides did not merge with manifest defaults.');
    }
    $resolver->reset($app);
    if ($resolver->resolve($app) !== $base || AppDefinition::$rows !== []) {
        throw new RuntimeException('Reset did not restore manifest defaults.');
    }
    $resolver->save($app, $base);
    if (AppDefinition::$rows !== []) {
        throw new RuntimeException('No-op save must not create an override row.');
    }
    echo "OK: override diff, persist, merge, reset and no-op checks passed.\n";
}
