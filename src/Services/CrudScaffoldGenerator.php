<?php

namespace Novay\MiniOS\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\MiniOS;
use Novay\MiniOS\Models\AppSidebarItem;
use ReflectionClass;
use RuntimeException;

/** Only generates host-project PHP/Blade placeholders for verified App\\MiniOS apps. */
class CrudScaffoldGenerator
{
    public function paths(DesktopApp $app, AppSidebarItem $item): array
    {
        if ($item->app_id !== $app->id() || $item->type !== 'crud' || in_array($app->id(), MiniOS::CORE_APPS, true)) {
            throw new RuntimeException('CRUD generator hanya tersedia untuk aplikasi custom milik project.');
        }
        if (! preg_match('/^[a-z][a-z0-9-]{0,79}$/D', $item->slug)) {
            throw new RuntimeException('Slug CRUD tidak valid.');
        }
        $appClass = $app::class;
        if (! preg_match('/^App\\\\MiniOS\\\\([A-Za-z][A-Za-z0-9]*)\\\\\1App$/D', $appClass, $m)) {
            throw new RuntimeException('Manifest harus berada pada App\\MiniOS\\{Name}\\{Name}App.');
        }
        $manifest = (new ReflectionClass($app))->getFileName();
        $expected = app_path('MiniOS/'.$m[1].'/'.$m[1].'App.php');
        $root = realpath(app_path());
        if (! $manifest || ! $root || realpath($manifest) !== realpath($expected) ||
            ! str_starts_with(realpath($expected) ?: '', $root.DIRECTORY_SEPARATOR.'MiniOS'.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Manifest aplikasi harus merupakan berkas lokal project Laravel.');
        }
        $class = Str::studly($item->slug);
        $namespace = 'App\\MiniOS\\'.$m[1].'\\Livewire\\'.$class;
        $phpDir = app_path('MiniOS/'.$m[1].'/Livewire/'.$class);
        $bladeDir = resource_path('views/apps/'.Str::kebab($m[1]).'/'.$item->slug);
        $paths = [];
        foreach (['Index', 'Create', 'Edit', 'Show'] as $page) {
            $paths[$page] = [
                'php' => $phpDir.'/'.$page.'.php',
                'blade' => $bladeDir.'/'.Str::kebab($page).'.blade.php',
                'namespace' => $namespace,
                'view' => 'apps.'.Str::kebab($m[1]).'.'.$item->slug.'.'.Str::kebab($page),
            ];
        }

        return $paths;
    }

    public function generate(DesktopApp $app, AppSidebarItem $item): array
    {
        // Disabled by default in production; explicitly enable after reviewing server permissions.
        if (! config('minios.allow_web_scaffolding', app()->environment('local'))) {
            throw new RuntimeException('Web scaffolding dinonaktifkan. Aktifkan MINIOS_ALLOW_WEB_SCAFFOLDING bila diperlukan.');
        }
        $paths = $this->paths($app, $item);
        foreach ($paths as $pair) {
            foreach (['php', 'blade'] as $kind) {
                if (file_exists($pair[$kind]) || is_link($pair[$kind])) {
                    throw new RuntimeException('Generator dibatalkan: file sudah ada: '.$pair[$kind]);
                }
            }
        }
        $created = [];
        try {
            foreach ($paths as $page => $pair) {
                foreach (['php', 'blade'] as $kind) {
                    $target = $pair[$kind];
                    $parent = dirname($target);
                    if (! is_dir($parent) && ! File::makeDirectory($parent, 0755, true) && ! is_dir($parent)) {
                        throw new RuntimeException('Tidak dapat membuat direktori '.$parent);
                    }
                    $allowedRoot = realpath($kind === 'php' ? app_path() : resource_path('views'));
                    $actualParent = realpath($parent);
                    if (! $allowedRoot || ! $actualParent || ! str_starts_with($actualParent, $allowedRoot.DIRECTORY_SEPARATOR)) {
                        throw new RuntimeException('Direktori generator berada di luar root project.');
                    }
                    $content = $kind === 'php' ? $this->phpSource($page, $pair['namespace'], $pair['view']) : $this->bladeSource($page, $item->label, $item->slug, $app->id(), MiniOS::path($item->route_prefix));
                    // Exclusive create: never modify any preexisting application code.
                    $stream = @fopen($target, 'x');
                    if (! $stream) {
                        throw new RuntimeException('File sudah ada/tidak bisa ditulis: '.$target);
                    }
                    $created[] = $target;
                    try {
                        $written = fwrite($stream, $content);
                        if ($written !== strlen($content)) {
                            throw new RuntimeException('Gagal menulis file '.$target);
                        }
                    } finally {
                        fclose($stream);
                    }
                }
            }
        } catch (\Throwable $error) {
            foreach ($created as $path) {
                @unlink($path);
            }
            throw $error;
        }

        return $created;
    }

    private function phpSource(string $page, string $namespace, string $view): string
    {
        $recordProperty = in_array($page, ['Edit', 'Show'], true) ? "    public ?string \$recordId = null;\n\n" : '';

        return <<<PHP
<?php

namespace {$namespace};

use Livewire\\Component;

/** Placeholder generated by MiniOS. Implement model binding and authorisation here. */
class {$page} extends Component
{
{$recordProperty}    public function render()
    {
        return view('{$view}');
    }
}
PHP;
    }

    private function bladeSource(string $page, string $label, string $slug, string $appId, string $baseUrl): string
    {
        $title = htmlspecialchars($label.' — '.$page, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $description = match ($page) {
            'Index' => 'Tambahkan tabel, pencarian, dan pagination untuk data ini.',
            'Create' => 'Bangun form create dan validasi server sesuai model aplikasi.',
            'Edit' => 'Bangun form edit beserta otorisasi per record.',
            'Show' => 'Bangun detail record beserta otorisasi per record.',
        };
        $appJs = htmlspecialchars(json_encode($appId, JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
        $indexJs = htmlspecialchars(json_encode($baseUrl, JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
        $createJs = htmlspecialchars(json_encode(rtrim($baseUrl, '/').'/create', JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
        $destination = $page === 'Index' ? $createJs : $indexJs;
        $button = $page === 'Index' ? 'Create New' : 'Back to Index';
        $record = in_array($page, ['Edit', 'Show'], true) ? '<p class="text-xs text-neutral-500">Record ID: {{ $recordId }}</p>' : '';

        return <<<BLADE
<div class="space-y-4 p-6">
    <header class="flex flex-wrap items-center justify-between gap-3">
        <div class="space-y-1">
            <h2 class="text-xl font-semibold">{$title}</h2>
            <p class="text-sm text-neutral-500">{$description}</p>
            {$record}
        </div>
        <button type="button"
            x-on:click="\$dispatch('update-window-url', {id: {$appJs}, url: {$destination}}); \$dispatch('minios-app-navigate', {id: {$appJs}, path: {$destination}})"
            class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-500">{$button}</button>
    </header>
    <section class="rounded-xl border border-dashed border-neutral-300 p-6 text-sm text-neutral-500 dark:border-white/15">
        TODO: Implement the {$page} page in your Livewire component.
        {{-- Keep authorization and record ownership checks on the server. --}}
    </section>
</div>
BLADE;
    }
}
