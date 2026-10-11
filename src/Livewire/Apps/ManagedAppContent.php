<?php

namespace Novay\MiniOS\Livewire\Apps;

use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Livewire;
use Novay\MiniOS\MiniOS;
use Novay\MiniOS\Services\AppDefinitionResolver;
use Novay\MiniOS\Services\AppSidebarService;
use Novay\MiniOS\Support\AppRegistry;

/** Route-aware content host for apps using the managed sidebar. */
class ManagedAppContent extends Component
{
    #[Locked]
    public string $appId = '';

    #[Locked]
    public string $path = '';

    public function mount(string $appId): void
    {
        $app = app(AppRegistry::class)->get($appId) ?? abort(404);
        $this->appId = $appId;
        $service = app(AppSidebarService::class);
        $current = request()->getPathInfo();
        $relative = $service->relativePath($current);
        $this->path = $service->page($app, $current) ? MiniOS::path($relative) : MiniOS::path(app(AppDefinitionResolver::class)->resolve($app)['entry']);
    }

    public function navigate(string $path): void
    {
        $app = app(AppRegistry::class)->get($this->appId) ?? abort(404);
        abort_unless((bool) (app(AppDefinitionResolver::class)->resolve($app)['layout']['sidebar_enabled'] ?? false), 403);
        $service = app(AppSidebarService::class);
        $relative = $service->relativePath($path);
        abort_unless($service->page($app, $path), 404);
        $this->path = MiniOS::path($relative);
    }

    /**
     * Safe fallback handler for managed content refresh requests.
     */
    public function refresh(): void
    {
        // No-op.
    }

    public function render()
    {
        $app = app(AppRegistry::class)->get($this->appId) ?? abort(404);
        $service = app(AppSidebarService::class);
        $page = $service->page($app, $this->path);
        $component = $page['component'] ?? null;
        $supported = is_string($component) && class_exists($component) && is_subclass_of($component, Component::class);

        if ($supported && class_exists(Livewire::class)) {
            Livewire::component($component);
        }

        return view('minios::apps.managed-content', [
            'appId' => $this->appId,
            'path' => $this->path,
            'page' => $page,
            'component' => $component,
            'supported' => $supported,
            'recordId' => $page['recordId'] ?? null,
            'pathKey' => $this->path,
            'basePath' => MiniOS::path(app(AppDefinitionResolver::class)->resolve($app)['entry']),
        ]);
    }
}
