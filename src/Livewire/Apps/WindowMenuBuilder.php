<?php

namespace Novay\MiniOS\Livewire\Apps;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\MiniOS;
use Novay\MiniOS\Models\AppMenu;
use Novay\MiniOS\Services\AppDefinitionResolver;
use Novay\MiniOS\Services\AppMenuService;
use Novay\MiniOS\Support\AppRegistry;

class WindowMenuBuilder extends Component
{
    #[Locked]
    public string $appId;

    #[Locked]
    public ?int $editingId = null;

    #[Locked]
    public ?int $parentId = null;

    public array $menuForm = [];

    public string $permissionsText = '';

    public ?string $flash = null;

    public function mount(string $appId): void
    {
        Gate::authorize('minios.manage-apps');
        abort_unless(app(AppRegistry::class)->get($appId), 404);
        $this->appId = $appId;
        $this->resetMenuForm();
    }

    public function initialize(): void
    {
        $this->authorizeBuilder();
        app(AppMenuService::class)->startEditing($this->app());
        $this->flash = 'Menu standar siap dikelola. Tambahkan atau ubah item di bawah.';
    }

    public function add(?int $parentId = null): void
    {
        $this->authorizeBuilder();
        $this->requireDatabase();
        if ($parentId !== null) {
            $parent = $this->owned($parentId);
            abort_unless($parent->action_type === null && $this->depth($parent) < 3, 422);
        }
        $this->resetMenuForm();
        $this->parentId = $parentId;
        $this->menuForm['kind'] = $parentId === null ? 'group' : 'action';
    }

    public function edit(int $id): void
    {
        $this->authorizeBuilder();
        $row = $this->owned($id);
        $this->editingId = $row->id;
        $this->parentId = $row->parent_id;
        $this->menuForm = [
            'label' => $row->label,
            'kind' => $row->action_type === null ? 'group' : ($row->action_type === 'separator' ? 'separator' : 'action'),
            'icon' => $row->icon ?? '',
            'shortcut' => $row->shortcut ?? '',
            'action_type' => in_array($row->action_type, AppMenuService::ACTIONS, true) ? $row->action_type : 'window.refresh',
            'path' => (string) ($row->action_payload['path'] ?? ''),
            'event' => (string) ($row->action_payload['event'] ?? ''),
            'enabled' => $row->enabled,
        ];
        $this->permissionsText = implode("\n", $row->permissions ?? []);
        $this->flash = null;
    }

    public function saveMenu(): void
    {
        $this->authorizeBuilder();
        $this->requireDatabase();
        $data = $this->validate([
            'menuForm.label' => ['nullable', 'string', 'max:120'],
            'menuForm.kind' => ['required', 'in:group,action,separator'],
            'menuForm.icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9\-]*$/'],
            'menuForm.shortcut' => ['nullable', 'string', 'max:35'],
            'menuForm.action_type' => ['nullable', 'string', 'in:'.implode(',', AppMenuService::ACTIONS)],
            'menuForm.path' => ['nullable', 'string', 'max:250'],
            'menuForm.event' => ['nullable', 'string', 'max:100'],
            'menuForm.enabled' => ['required', 'boolean'],
            'permissionsText' => ['nullable', 'string', 'max:2000'],
        ])['menuForm'];

        $kind = $data['kind'];
        $label = trim($data['label'] ?? '');
        if ($kind !== 'separator' && $label === '') {
            throw ValidationException::withMessages(['menuForm.label' => 'Label harus diisi.']);
        }
        $permissions = array_values(array_unique(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $this->permissionsText) ?: []))));
        if (count($permissions) > 20 || collect($permissions)->contains(fn ($p) => ! preg_match('/^[a-zA-Z0-9:._-]{1,100}$/D', $p))) {
            throw ValidationException::withMessages(['permissionsText' => 'Maksimal 20 permission, satu ability valid per baris.']);
        }
        $action = $kind === 'group' ? null : ($kind === 'separator' ? 'separator' : ($data['action_type'] ?? null));
        if ($kind === 'action' && ! in_array($action, AppMenuService::ACTIONS, true)) {
            throw ValidationException::withMessages(['menuForm.action_type' => 'Pilih action yang didukung.']);
        }
        $payload = [];
        if ($action === 'app.navigate') {
            $path = trim($data['path'] ?? '');
            $owned = app(AppDefinitionResolver::class)->resolve($this->app())['routes'];
            if (! preg_match('~^/(?!/)[a-zA-Z0-9/_-]+$~D', $path) || str_contains($path, '..') ||
                ! collect($owned)->contains(fn ($route) => $path === $route || str_starts_with($path, rtrim($route, '/').'/'))) {
                throw ValidationException::withMessages(['menuForm.path' => 'Path wajib berada dalam Owned Routes aplikasi.']);
            }
            $payload['path'] = MiniOS::path($path);
        }
        if ($action === 'app.event') {
            $event = trim($data['event'] ?? '');
            if (! preg_match('/^[a-z][a-z0-9-]{0,79}$/D', $event)) {
                throw ValidationException::withMessages(['menuForm.event' => 'Event harus berupa slug, contoh: open-settings.']);
            }
            $payload['event'] = $event;
        }

        if ($this->parentId !== null && $kind === 'group' && $this->depth($this->owned($this->parentId)) >= 2) {
            throw ValidationException::withMessages(['menuForm.kind' => 'Kedalaman submenu maksimal tiga tingkat; tingkat terakhir harus berupa aksi atau separator.']);
        }

        if ($this->editingId === null) {
            if ($this->parentId !== null) {
                $parent = $this->owned($this->parentId);
                abort_unless($parent->action_type === null && $this->depth($parent) < 3, 422);
            } elseif ($kind !== 'group') {
                throw ValidationException::withMessages(['menuForm.kind' => 'Menu tingkat atas harus berupa kelompok (group).']);
            }
        } else {
            $existing = $this->owned($this->editingId);
            if ($existing->parent_id === null && $kind !== 'group') {
                throw ValidationException::withMessages(['menuForm.kind' => 'Menu tingkat atas harus berupa kelompok.']);
            }
            if ($kind !== 'group' && AppMenu::query()->where('app_id', $this->appId)->where('parent_id', $existing->id)->exists()) {
                throw ValidationException::withMessages(['menuForm.kind' => 'Pindahkan/hapus submenu sebelum mengubah kelompok menjadi aksi.']);
            }
        }

        DB::transaction(function () use ($data, $kind, $label, $action, $payload, $permissions) {
            $row = $this->editingId !== null ? $this->owned($this->editingId) : new AppMenu([
                'app_id' => $this->appId,
                'parent_id' => $this->parentId,
                'sort_order' => (int) AppMenu::query()->where('app_id', $this->appId)->where('parent_id', $this->parentId)->max('sort_order') + 1,
            ]);
            $row->fill([
                'label' => $kind === 'separator' ? '—' : $label,
                'icon' => ($kind === 'action' && filled($data['icon'] ?? '')) ? $data['icon'] : null,
                'shortcut' => ($kind === 'action' && filled($data['shortcut'] ?? '')) ? $data['shortcut'] : null,
                'action_type' => $action,
                'action_payload' => $payload,
                'permissions' => $permissions,
                'enabled' => $data['enabled'],
            ]);
            $row->save();
        });
        $parentId = $this->parentId;
        $this->resetMenuForm();
        $this->parentId = $parentId;
        $this->menuForm['kind'] = $parentId === null ? 'group' : 'action';
        $this->flash = 'Item menu tersimpan. Reload desktop untuk melihat hasilnya.';
    }

    public function deleteMenu(int $id): void
    {
        $this->authorizeBuilder();
        $this->owned($id)->delete(); // FK cascades descendants.
        $this->resetMenuForm();
        $this->flash = 'Item berhasil dihapus.';
    }

    public function move(int $id, int $direction): void
    {
        $this->authorizeBuilder();
        abort_unless(in_array($direction, [-1, 1], true), 422);
        DB::transaction(function () use ($id, $direction) {
            $item = $this->owned($id);
            $siblings = AppMenu::query()->where('app_id', $this->appId)
                ->where('parent_id', $item->parent_id)->orderBy('sort_order')->orderBy('id')->lockForUpdate()->get();
            $idx = $siblings->search(fn ($s) => $s->id === $item->id);
            $next = $idx + $direction;
            if ($next < 0 || $next >= $siblings->count()) {
                return;
            }
            $ordered = $siblings->all();
            [$ordered[$idx], $ordered[$next]] = [$ordered[$next], $ordered[$idx]];
            foreach ($ordered as $pos => $model) {
                $model->sort_order = $pos;
                $model->save();
            }
        });
        $this->flash = 'Urutan menu diperbarui.';
    }

    public function resetMenus(): void
    {
        $this->authorizeBuilder();
        $this->requireDatabase();
        app(AppMenuService::class)->reset($this->app());
        $this->resetMenuForm();
        $this->flash = 'Menu dikembalikan ke default virtual.';
    }

    public function render()
    {
        $authorized = Gate::allows('minios.manage-apps');
        $service = app(AppMenuService::class);

        return view('minios::apps.window-manager.menu-builder', [
            'authorized' => $authorized,
            'ready' => $authorized && $service->hasDatabase(),
            'customized' => $authorized && $service->isCustomized($this->appId),
            'tree' => $authorized ? $service->tree($this->app(), false) : [],
        ]);
    }

    private function owned(int $id): AppMenu
    {
        return AppMenu::query()->where('app_id', $this->appId)->whereKey($id)->firstOrFail();
    }

    private function depth(AppMenu $node): int
    {
        $level = 1;
        while ($node->parent_id !== null && $level <= 3) {
            $node = $this->owned($node->parent_id);
            $level++;
        }

        return $level;
    }

    private function authorizeBuilder(): void
    {
        Gate::authorize('minios.manage-apps');
        abort_unless($this->app(), 404);
    }

    private function app(): DesktopApp
    {
        return app(AppRegistry::class)->get($this->appId) ?? abort(404);
    }

    private function requireDatabase(): void
    {
        if (! app(AppMenuService::class)->hasDatabase()) {
            throw ValidationException::withMessages(['menuForm.label' => 'Jalankan php artisan migrate terlebih dahulu.']);
        }
    }

    private function resetMenuForm(): void
    {
        $this->editingId = null;
        $this->parentId = null;
        $this->menuForm = [
            'label' => '', 'kind' => 'group', 'icon' => '', 'shortcut' => '',
            'action_type' => 'window.refresh', 'path' => '', 'event' => '', 'enabled' => true,
        ];
        $this->permissionsText = '';
        $this->resetValidation();
    }

    /**
     * Safe fallback handler for refresh requests.
     */
    public function refresh(): void
    {
        // No-op.
    }
}
