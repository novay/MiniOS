<?php

namespace Novay\MiniOS\Livewire\Apps;

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\MiniOS;
use Novay\MiniOS\Services\AppDefinitionResolver;
use Novay\MiniOS\Services\AppStatusbarService;
use Novay\MiniOS\Support\AppRegistry;

class WindowManager extends Component
{
    #[Url(as: 'app')]
    public string $selectedId = '';

    public string $search = '';

    #[Url(as: 'tab')]
    public string $section = 'properties';

    public array $form = [];

    public string $routesText = '';

    public ?string $message = null;

    public string $messageType = 'success';

    public function mount(): void
    {
        if (! Gate::allows('minios.manage-apps')) {
            return;
        }

        if (! in_array($this->section, ['properties', 'layout', 'menus', 'sidebar', 'widgets'], true)) {
            $this->section = 'properties';
        }

        $registry = app(AppRegistry::class);
        if ($this->selectedId !== '' && $registry->get($this->selectedId)) {
            $this->selectApp($this->selectedId);

            return;
        }

        $ids = array_keys($registry->all());
        $preferred = null;
        foreach ($ids as $id) {
            if (! in_array($id, MiniOS::CORE_APPS, true)) {
                $preferred = $id;
                break;
            }
        }
        $targetId = $preferred ?? ($ids[0] ?? null);
        if ($targetId) {
            $this->selectApp($targetId);
        }
    }

    public function selectApp(string $id): void
    {
        Gate::authorize('minios.manage-apps');
        $app = app(AppRegistry::class)->get($id);
        abort_unless($app, 404);

        $this->selectedId = $id;
        $this->message = null;
        $this->resetValidation();
        $this->fillForm($app);
    }

    public function refresh(): void
    {
        Gate::authorize('minios.manage-apps');
        $this->message = null;
        $this->resetValidation();
        if ($this->selectedId !== '') {
            $app = app(AppRegistry::class)->get($this->selectedId);
            if ($app) {
                $this->fillForm($app);
            }
        }
    }

    public function setSection(string $section): void
    {
        Gate::authorize('minios.manage-apps');
        if (in_array($section, ['properties', 'layout', 'menus', 'sidebar', 'widgets'], true)) {
            $this->section = $section;
        }
    }

    public function save(): void
    {
        Gate::authorize('minios.manage-apps');
        $app = $this->selectedApp();
        $resolver = app(AppDefinitionResolver::class);

        if (! $resolver->hasDatabase()) {
            $this->setMessage('Tabel konfigurasi belum tersedia. Jalankan php artisan migrate.', 'error');

            return;
        }

        $input = $this->validate([
            'form.name' => ['required', 'string', 'max:120'],
            'form.icon' => ['required', 'string', 'max:250'],
            'form.entry' => ['required', 'string', 'max:255'],
            'form.component' => ['nullable', 'string', 'max:255'],
            'form.pinned' => ['required', 'boolean'],
            'form.window.width' => ['required', 'integer', 'between:200,5000'],
            'form.window.height' => ['required', 'integer', 'between:200,5000'],
            'form.window.min_width' => ['required', 'integer', 'between:200,5000'],
            'form.window.min_height' => ['required', 'integer', 'between:200,5000'],
            'form.window.max_width' => ['nullable', 'integer', 'between:200,10000'],
            'form.window.max_height' => ['nullable', 'integer', 'between:200,10000'],
            'form.window.resizable' => ['required', 'boolean'],
            'form.window.maximizable' => ['required', 'boolean'],
            'form.window.center' => ['required', 'boolean'],
            'form.layout.shell_mode' => ['required', 'in:managed,embedded'],
            'form.layout.menubar_enabled' => ['required', 'boolean'],
            'form.layout.sidebar_enabled' => ['required', 'boolean'],
            'form.layout.statusbar_enabled' => ['required', 'boolean'],
            'routesText' => ['nullable', 'string', 'max:4000'],
        ]);

        $data = $input['form'];
        $data['name'] = trim($data['name']);
        $data['icon'] = trim($data['icon']);
        $data['entry'] = trim($data['entry']);
        $data['component'] = filled($data['component'] ?? null) ? trim($data['component']) : null;

        if ($data['name'] === '' || $data['icon'] === '') {
            throw ValidationException::withMessages(['form.name' => 'Nama dan ikon tidak boleh kosong.']);
        }

        if (! preg_match('~^(?:[a-z][a-z0-9-]*|/[^\s\"<>]+|https?://[^\s\"<>]+|[a-z0-9_-]+\.(?:png|webp|svg))$~iD', $data['icon'])) {
            throw ValidationException::withMessages(['form.icon' => 'Gunakan nama ikon, URL http(s), atau path aset gambar yang valid.']);
        }

        $routes = preg_split('/\r\n|\r|\n/', $this->routesText) ?: [];
        $routes = array_values(array_unique(array_filter(array_map('trim', $routes), 'strlen')));

        if (count($routes) > 40) {
            throw ValidationException::withMessages(['routesText' => 'Maksimal 40 route per aplikasi.']);
        }

        foreach ([$data['entry'], ...$routes] as $route) {
            if (! preg_match('~^/(?!/)[a-zA-Z0-9/_-]+$~D', $route) || str_contains($route, '..') ||
                in_array($route, ['/login', '/register', '/lock', '/logout', '/desktop'], true) ||
                str_starts_with($route, '/minios/')) {
                throw ValidationException::withMessages(['routesText' => 'Entry dan routes harus berupa path internal, contoh: /todo/tasks.']);
            }
        }

        $window = $data['window'];
        if ($window['min_width'] > $window['width'] || $window['min_height'] > $window['height'] ||
            ($window['max_width'] !== null && $window['max_width'] !== '' && (int) $window['max_width'] < $window['width']) ||
            ($window['max_height'] !== null && $window['max_height'] !== '' && (int) $window['max_height'] < $window['height'])) {
            throw ValidationException::withMessages(['form.window.width' => 'Ukuran harus memenuhi minimum ≤ default ≤ maksimum.']);
        }

        // Core apps may change appearance/window/layout, but not executable targets.
        $defaults = $resolver->defaults($app);
        if (app(MiniOS::class)->isCoreApp($app->id())) {
            $data['layout']['shell_mode'] = 'embedded';
            $data['entry'] = $defaults['entry'];
            $data['routes'] = $defaults['routes'];
            $data['component'] = $defaults['component'];
        } else {
            if (! in_array($data['entry'], $routes, true)) {
                throw ValidationException::withMessages(['routesText' => 'Owned Routes harus mencakup Entry Path aplikasi.']);
            }
            $data['routes'] = $routes;
            $proposed = array_map($this->canonicalPath(...), $routes);
            foreach (app(AppRegistry::class)->all() as $otherId => $other) {
                if ($otherId === $app->id()) {
                    continue;
                }
                $otherRoutes = $resolver->resolve($other)['routes'];
                foreach ($otherRoutes as $otherRoute) {
                    if (in_array($this->canonicalPath($otherRoute), $proposed, true)) {
                        throw ValidationException::withMessages(['routesText' => "Route {$otherRoute} sudah digunakan aplikasi {$otherId}."]);
                    }
                }
            }
            if ($data['component'] !== $defaults['component'] && $data['component'] !== null &&
                (! class_exists($data['component']) || ! is_subclass_of($data['component'], Component::class))) {
                throw ValidationException::withMessages(['form.component' => 'Component baru harus berupa nama class Livewire yang sudah ada.']);
            }
        }

        foreach (['width', 'height', 'min_width', 'min_height'] as $key) {
            $window[$key] = (int) $window[$key];
        }
        foreach (['max_width', 'max_height'] as $key) {
            $window[$key] = ($window[$key] === null || $window[$key] === '') ? null : (int) $window[$key];
        }
        $data['window'] = $window;

        $resolver->save($app, $data, auth()->id() !== null ? (int) auth()->id() : null);
        $this->fillForm($app);
        $this->setMessage('Konfigurasi tersimpan. Muat ulang desktop untuk menerapkan perubahan pada jendela.', 'success');
    }

    public function resetToDefaults(): void
    {
        Gate::authorize('minios.manage-apps');
        $app = $this->selectedApp();
        $resolver = app(AppDefinitionResolver::class);
        if (! $resolver->hasDatabase()) {
            $this->setMessage('Jalankan php artisan migrate terlebih dahulu.', 'error');

            return;
        }
        $resolver->reset($app);
        $this->fillForm($app);
        $this->setMessage('Override telah dihapus. Default PHP manifest berlaku setelah desktop dimuat ulang.', 'success');
    }

    public function render()
    {
        $authorized = Gate::allows('minios.manage-apps');
        $registry = app(AppRegistry::class);
        $resolver = app(AppDefinitionResolver::class);
        $apps = [];

        if ($authorized) {
            foreach ($registry->all() as $id => $app) {
                $info = $resolver->resolve($app);
                if ($this->search !== '' && stripos($id.' '.$info['name'], $this->search) === false) {
                    continue;
                }
                $apps[] = [
                    'id' => $id,
                    'name' => $info['name'],
                    'icon' => $info['icon'],
                    'core' => in_array($id, MiniOS::CORE_APPS, true),
                    'modified' => $resolver->hasOverride($id),
                ];
            }
        }

        return view('minios::apps.window-manager.index', [
            'authorized' => $authorized,
            'apps' => $apps,
            'isCore' => in_array($this->selectedId, MiniOS::CORE_APPS, true),
            'hasDatabase' => $authorized && $resolver->hasDatabase(),
            'hasStatusbarTemplate' => $authorized && $this->selectedId !== '' && app(AppStatusbarService::class)->customView($this->selectedId) !== null,
        ]);
    }

    private function canonicalPath(string $route): string
    {
        $route = '/'.ltrim($route, '/');
        $prefix = MiniOS::prefix();
        if ($prefix === '') {
            return rtrim($route, '/') ?: '/';
        }
        if ($route === "/{$prefix}" || str_starts_with($route, "/{$prefix}/")) {
            return rtrim($route, '/');
        }
        if (str_starts_with($route, '/desktop/')) {
            $route = substr($route, 8);
        }

        return rtrim("/{$prefix}/".ltrim($route, '/'), '/');
    }

    private function selectedApp(): DesktopApp
    {
        $app = app(AppRegistry::class)->get($this->selectedId);
        abort_unless($app, 404);

        return $app;
    }

    private function fillForm(DesktopApp $app): void
    {
        $config = app(AppDefinitionResolver::class)->resolve($app);
        $this->form = $config;
        $this->form['window'] = array_merge([
            'width' => 900, 'height' => 600, 'min_width' => 500, 'min_height' => 350,
            'max_width' => null, 'max_height' => null, 'center' => false,
            'resizable' => true, 'maximizable' => true,
        ], $config['window']);
        $this->routesText = implode("\n", $config['routes']);
    }

    private function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }
}
