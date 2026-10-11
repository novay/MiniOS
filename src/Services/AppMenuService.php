<?php

namespace Novay\MiniOS\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Models\AppMenu;
use Novay\MiniOS\Models\AppMenuState;

/**
 * A bounded, declarative menu tree. No executable Blade/JS is stored in the DB.
 * Until edited, applications receive virtual defaults and generate zero records.
 */
class AppMenuService
{
    public const ACTIONS = [
        'window.close', 'sidebar.toggle', 'window.refresh', 'statusbar.toggle',
        'help.about', 'app.navigate', 'app.event',
    ];

    public function hasDatabase(): bool
    {
        try {
            return Schema::hasTable('minios_app_menus') && Schema::hasTable('minios_app_menu_states');
        } catch (\Throwable) {
            return false;
        }
    }

    public function defaults(DesktopApp $app): array
    {
        $config = app(AppDefinitionResolver::class)->resolve($app);
        $sidebar = (bool) ($config['layout']['sidebar_enabled'] ?? false);
        $statusbar = (bool) ($config['layout']['statusbar_enabled'] ?? false);

        return [
            ['label' => 'File', 'children' => [
                ['label' => 'Close', 'icon' => 'x-mark', 'shortcut' => 'Ctrl+W', 'action_type' => 'window.close'],
            ]],
            ['label' => 'View', 'children' => $sidebar ? [
                ['label' => 'Toggle Sidebar', 'icon' => 'bars-3-bottom-left', 'shortcut' => 'Ctrl+B', 'action_type' => 'sidebar.toggle'],
            ] : []],
            ['label' => 'Window', 'children' => array_merge([
                ['label' => 'Reload Page', 'icon' => 'arrow-path', 'shortcut' => 'Ctrl+R', 'action_type' => 'window.refresh'],
            ], $statusbar ? [
                ['label' => 'Toggle Statusbar', 'icon' => 'rectangle-stack', 'shortcut' => 'Ctrl+P', 'action_type' => 'statusbar.toggle'],
            ] : [])],
            ['label' => 'Help', 'children' => [
                ['label' => 'About', 'icon' => 'information-circle', 'action_type' => 'help.about'],
            ]],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function tree(DesktopApp $app, bool $filterPermissions = true): array
    {
        $id = $app->id();
        $rows = $this->isCustomized($id)
            ? AppMenu::query()->where('app_id', $id)->orderBy('sort_order')->orderBy('id')->get()->map(fn ($m) => [
                'id' => $m->id,
                'parent_id' => $m->parent_id,
                'label' => $m->label,
                'icon' => $m->icon,
                'shortcut' => $m->shortcut,
                'action_type' => $m->action_type,
                'action_payload' => $m->action_payload ?? [],
                'permissions' => $m->permissions ?? [],
                'enabled' => $m->enabled,
                'sort_order' => $m->sort_order,
            ])->all()
            : $this->defaults($app);

        $nested = $this->buildTree($rows);

        return $filterPermissions ? $this->filterTree($nested, $app) : $nested;
    }

    public function isCustomized(string $appId): bool
    {
        return $this->hasDatabase() && AppMenuState::query()->whereKey($appId)->exists();
    }

    public function startEditing(DesktopApp $app): void
    {
        if (! $this->hasDatabase()) {
            throw new \RuntimeException('Jalankan php artisan migrate sebelum mengubah menu.');
        }
        DB::transaction(function () use ($app) {
            if (AppMenuState::query()->whereKey($app->id())->lockForUpdate()->exists()) {
                return;
            }
            AppMenuState::query()->create(['app_id' => $app->id()]);
            $position = 0;
            foreach ($this->defaults($app) as $root) {
                $this->insertDefault($app->id(), $root, null, $position++);
            }
        });
    }

    public function reset(DesktopApp $app): void
    {
        DB::transaction(function () use ($app) {
            AppMenu::query()->where('app_id', $app->id())->delete();
            AppMenuState::query()->whereKey($app->id())->delete();
        });
    }

    public function clearAfterMutation(): void
    {
        // Query is deliberately not cached across Livewire actions.
    }

    private function insertDefault(string $appId, array $item, ?int $parentId, int $position): int
    {
        $model = AppMenu::query()->create([
            'app_id' => $appId,
            'parent_id' => $parentId,
            'label' => $item['label'],
            'icon' => $item['icon'] ?? null,
            'shortcut' => $item['shortcut'] ?? null,
            'action_type' => $item['action_type'] ?? null,
            'action_payload' => $item['action_payload'] ?? [],
            'permissions' => $item['permissions'] ?? [],
            'sort_order' => $position,
            'enabled' => true,
        ]);
        foreach ($item['children'] ?? [] as $idx => $child) {
            $this->insertDefault($appId, $child, $model->id, $idx);
        }

        return $model->id;
    }

    private function buildTree(array $rows): array
    {
        // Virtual defaults are already nested.
        if ($rows === [] || ! array_key_exists('parent_id', $rows[0])) {
            return $rows;
        }
        $children = [];
        $known = array_fill_keys(array_column($rows, 'id'), true);
        foreach ($rows as $row) {
            $parent = $row['parent_id'];
            if ($parent !== null && ! isset($known[$parent])) {
                continue; // Orphaned or stale data cannot become a root action.
            }
            $children[$parent ?? 0][] = $row;
        }
        $walk = function ($parent, $depth) use (&$walk, $children) {
            if ($depth > 3) {
                return [];
            }
            $items = [];
            foreach ($children[$parent] ?? [] as $row) {
                $row['children'] = $walk($row['id'], $depth + 1);
                $items[] = $row;
            }

            return $items;
        };

        return $walk(0, 1);
    }

    private function filterTree(array $tree, DesktopApp $app): array
    {
        $layout = app(AppDefinitionResolver::class)->resolve($app)['layout'];
        $output = [];
        foreach ($tree as $node) {
            if (($node['enabled'] ?? true) === false) {
                continue;
            }
            $permissions = $node['permissions'] ?? [];
            $denied = false;
            foreach (is_array($permissions) ? $permissions : [] as $ability) {
                if (! is_string($ability) || ! Gate::allows($ability)) {
                    $denied = true;
                    break;
                }
            }
            if ($denied) {
                continue;
            }
            $action = $node['action_type'] ?? null;
            if ($action === 'sidebar.toggle' && ! ($layout['sidebar_enabled'] ?? false)) {
                continue;
            }
            if ($action === 'statusbar.toggle' && ! ($layout['statusbar_enabled'] ?? false)) {
                continue;
            }
            if ($action !== null && $action !== 'separator' && ! in_array($action, self::ACTIONS, true)) {
                continue;
            }
            $node['children'] = $this->filterTree($node['children'] ?? [], $app);
            if ($action === null && $node['children'] === []) {
                continue;
            }
            $output[] = $node;
        }

        return $output;
    }
}
