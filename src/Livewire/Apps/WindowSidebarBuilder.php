<?php

namespace Novay\MiniOS\Livewire\Apps;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\MiniOS;
use Novay\MiniOS\Models\AppSidebarItem;
use Novay\MiniOS\Services\AppDefinitionResolver;
use Novay\MiniOS\Services\AppSidebarService;
use Novay\MiniOS\Services\CrudScaffoldGenerator;
use Novay\MiniOS\Support\AppRegistry;

class WindowSidebarBuilder extends Component
{
    #[Locked]
    public string $appId = '';

    #[Locked]
    public ?int $editingId = null;

    public array $itemForm = [];

    public string $permissionsText = '';

    public ?string $flash = null;

    public string $flashType = 'success';

    public function mount(string $appId): void
    {
        Gate::authorize('minios.manage-apps');
        abort_unless(app(AppRegistry::class)->get($appId), 404);
        $this->appId = $appId;
        $this->clearForm();
    }

    public function initialize(): void
    {
        $this->authorizeBuilder();
        app(AppSidebarService::class)->startEditing($this->app());
        $this->flash = 'Sidebar siap dikelola.';
        $this->flashType = 'success';
    }

    public function add(): void
    {
        $this->authorizeBuilder();
        $this->clearForm();
    }

    public function edit(int $id): void
    {
        $this->authorizeBuilder();
        $row = $this->owned($id);
        $this->editingId = $id;
        $this->itemForm = [
            'slug' => $row->slug, 'type' => $row->type, 'label' => $row->label,
            'description' => $row->description ?? '', 'icon' => $row->icon ?? '',
            'icon_color' => $row->icon_color ?? 'text-neutral-500', 'badge' => $row->badge ?? '',
            'route_prefix' => $row->route_prefix, 'enabled' => $row->enabled,
        ];
        $this->permissionsText = implode("\n", $row->permissions ?? []);
        $this->flash = null;
    }

    public function saveItem(): void
    {
        $this->authorizeBuilder();
        $this->requireDatabase();
        $data = $this->validate([
            'itemForm.slug' => ['required', 'string', 'max:80', 'regex:/^[a-z][a-z0-9-]*$/'],
            'itemForm.type' => ['required', 'in:default,crud'],
            'itemForm.label' => ['required', 'string', 'max:120'],
            'itemForm.description' => ['nullable', 'string', 'max:200'],
            'itemForm.icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]*$/'],
            'itemForm.icon_color' => ['nullable', 'string', 'max:60', 'regex:/^text-[a-z]+-[1-9]00$/'],
            'itemForm.badge' => ['nullable', 'string', 'max:40'],
            'itemForm.route_prefix' => ['required', 'string', 'max:250'],
            'itemForm.enabled' => ['required', 'boolean'],
            'permissionsText' => ['nullable', 'string', 'max:2000'],
        ])['itemForm'];

        $app = $this->app();
        $entry = app(AppDefinitionResolver::class)->resolve($app)['entry'];
        $path = rtrim(trim($data['route_prefix']), '/') ?: '/';
        if (! preg_match('~^/(?!/)[a-zA-Z0-9/_-]+$~D', $path) || str_contains($path, '..') ||
            ($path !== $entry && ! str_starts_with($path, rtrim($entry, '/').'/'))) {
            throw ValidationException::withMessages(['itemForm.route_prefix' => 'Route harus berada di bawah entry aplikasi, misalnya '.$entry.'/tasks.']);
        }
        if ($data['type'] === 'crud' && ($path === $entry || preg_match('~/(?:create|edit)$~', $path))) {
            throw ValidationException::withMessages(['itemForm.route_prefix' => 'CRUD membutuhkan prefix unik di bawah entry aplikasi.']);
        }
        $permissions = array_values(array_unique(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $this->permissionsText) ?: []))));
        if (count($permissions) > 20 || collect($permissions)->contains(fn ($ability) => ! preg_match('/^[a-zA-Z0-9:._-]{1,100}$/D', $ability))) {
            throw ValidationException::withMessages(['permissionsText' => 'Maksimal 20 permission valid (satu per baris).']);
        }
        $service = app(AppSidebarService::class);
        if ($service->isReservedRoute($app, $path, $this->editingId)) {
            throw ValidationException::withMessages(['itemForm.route_prefix' => 'Route bentrok dengan navigasi aplikasi lain atau item yang sudah ada.']);
        }
        foreach ($service->items($app, false) as $other) {
            if ($this->editingId !== null && $other['id'] === $this->editingId) {
                continue;
            }
            // Default route ancestors (e.g. /todo) may contain CRUD routes (/todo/tasks).
            // Only reject intersections that would make a CRUD record path ambiguous.
            if ($service->conflictsWithSidebarItem($path, $data['type'], $other)) {
                throw ValidationException::withMessages(['itemForm.route_prefix' => 'Prefix CRUD tidak boleh bertumpang tindih dengan item lain.']);
            }
        }
        $slugExists = AppSidebarItem::query()->where('app_id', $this->appId)->where('slug', $data['slug'])
            ->when($this->editingId !== null, fn ($q) => $q->where('id', '!=', $this->editingId))->exists();
        if ($slugExists) {
            throw ValidationException::withMessages(['itemForm.slug' => 'Slug sudah digunakan di aplikasi ini.']);
        }
        foreach ($service->items($app, false) as $other) {
            if ($other['id'] !== $this->editingId && $data['type'] === 'crud' && $other['type'] === 'crud' &&
                strtolower(Str::studly($other['slug'])) === strtolower(Str::studly($data['slug']))) {
                throw ValidationException::withMessages(['itemForm.slug' => 'Nama class CRUD bentrok dengan item lain.']);
            }
        }
        if ($this->editingId !== null) {
            $current = $this->owned($this->editingId);
            if (($current->options['generated'] ?? false) &&
                ($data['slug'] !== $current->slug || $data['type'] !== $current->type || $path !== $current->route_prefix)) {
                throw ValidationException::withMessages(['itemForm.slug' => 'Slug, jenis, dan route terkunci setelah source CRUD di-generate.']);
            }
        }
        DB::transaction(function () use ($data, $path, $permissions) {
            $row = $this->editingId !== null ? $this->owned($this->editingId) : new AppSidebarItem([
                'app_id' => $this->appId,
                'sort_order' => (int) AppSidebarItem::query()->where('app_id', $this->appId)->max('sort_order') + 1,
            ]);
            $row->fill([
                'slug' => $data['slug'], 'type' => $data['type'], 'label' => trim($data['label']),
                'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
                'icon' => filled($data['icon'] ?? null) ? $data['icon'] : null,
                'icon_color' => $data['icon_color'] ?: 'text-neutral-500',
                'badge' => filled($data['badge'] ?? null) ? trim($data['badge']) : null,
                'route_prefix' => $path, 'permissions' => $permissions, 'enabled' => $data['enabled'],
            ]);
            $row->save();
        });
        $this->clearForm();
        $this->flash = 'Sidebar item tersimpan. Reload desktop agar menu/route diperbarui.';
        $this->flashType = 'success';
    }

    public function generate(int $id): void
    {
        $this->authorizeBuilder();
        $row = $this->owned($id);
        abort_unless($row->type === 'crud', 422);
        if ($row->options['generated'] ?? false) {
            $this->flash = 'Item ini sudah pernah di-generate. Berkas developer tidak akan ditimpa.';
            $this->flashType = 'error';

            return;
        }
        try {
            $files = app(CrudScaffoldGenerator::class)->generate($this->app(), $row);
            $row->options = ['generated' => true, 'generated_at' => now()->toIso8601String(), 'files' => $files];
            $row->save();
            $this->flash = 'Berhasil generate '.count($files).' file. Jalankan composer dump-autoload jika dibutuhkan.';
            $this->flashType = 'success';
        } catch (\Throwable $error) {
            report($error);
            $this->flash = $error->getMessage();
            $this->flashType = 'error';
        }
    }

    public function deleteItem(int $id): void
    {
        $this->authorizeBuilder();
        $this->owned($id)->delete(); // Generated source is intentionally preserved.
        $this->clearForm();
        $this->flash = 'Item navigasi dihapus. Berkas source tidak dihapus.';
    }

    public function move(int $id, int $direction): void
    {
        $this->authorizeBuilder();
        abort_unless(in_array($direction, [-1, 1], true), 422);
        DB::transaction(function () use ($id, $direction) {
            $row = $this->owned($id);
            $list = AppSidebarItem::query()->where('app_id', $this->appId)
                ->orderBy('sort_order')->orderBy('id')->lockForUpdate()->get();
            $index = $list->search(fn ($item) => $item->id === $row->id);
            $new = $index + $direction;
            if ($new < 0 || $new >= $list->count()) {
                return;
            }
            $list = $list->all();
            [$list[$index], $list[$new]] = [$list[$new], $list[$index]];
            foreach ($list as $position => $item) {
                $item->sort_order = $position;
                $item->save();
            }
        });
    }

    public function resetSidebar(): void
    {
        $this->authorizeBuilder();
        $this->requireDatabase();
        app(AppSidebarService::class)->reset($this->app());
        $this->clearForm();
        $this->flash = 'Sidebar kembali ke default Home. Berkas source tidak dihapus.';
    }

    public function render()
    {
        $authorized = Gate::allows('minios.manage-apps');
        $service = app(AppSidebarService::class);

        return view('minios::apps.window-manager.sidebar-builder', [
            'authorized' => $authorized,
            'ready' => $authorized && $service->hasDatabase(),
            'customized' => $authorized && $service->isCustomized($this->appId),
            'items' => $authorized ? $service->items($this->app(), false) : [],
            'isCore' => in_array($this->appId, MiniOS::CORE_APPS, true),
        ]);
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

    private function owned(int $id): AppSidebarItem
    {
        return AppSidebarItem::query()->where('app_id', $this->appId)->whereKey($id)->firstOrFail();
    }

    private function requireDatabase(): void
    {
        if (! app(AppSidebarService::class)->hasDatabase()) {
            throw ValidationException::withMessages(['itemForm.label' => 'Jalankan php artisan migrate terlebih dahulu.']);
        }
        if (! app(AppSidebarService::class)->isCustomized($this->appId)) {
            throw ValidationException::withMessages(['itemForm.label' => 'Klik Mulai Edit untuk mengelola sidebar.']);
        }
    }

    private function clearForm(): void
    {
        $this->editingId = null;
        $entry = app(AppDefinitionResolver::class)->resolve($this->app())['entry'];
        $this->itemForm = [
            'slug' => '', 'type' => 'default', 'label' => '', 'description' => '', 'icon' => 'folder',
            'icon_color' => 'text-neutral-500', 'badge' => '', 'route_prefix' => rtrim($entry, '/').'/',
            'enabled' => true,
        ];
        $this->permissionsText = '';
        $this->resetValidation();
    }
}
