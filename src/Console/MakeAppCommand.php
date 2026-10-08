<?php

namespace Novay\MiniOS\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'minios:make-app {name : The name of the desktop application (e.g. Todo, Notes)} {--icon=apps : The icon identifier} {--pinned : Whether the application is pinned}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new MiniOS desktop application manifest and component';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $rawName = trim($this->argument('name'));
        $studlyName = Str::studly(str_replace(['App', 'app'], '', $rawName));
        $kebabName = Str::kebab($studlyName);
        $icon = $this->option('icon') ?: 'apps';
        $pinned = $this->option('pinned') ? 'true' : 'false';

        $appDir = app_path("MiniOS/{$studlyName}");
        if (! File::isDirectory($appDir)) {
            File::makeDirectory($appDir, 0755, true);
        }

        $manifestPath = "{$appDir}/{$studlyName}App.php";
        if (File::exists($manifestPath)) {
            $this->error("Application manifest [{$manifestPath}] already exists!");

            return Command::FAILURE;
        }

        $manifestContent = <<<PHP
<?php

namespace App\MiniOS\\{$studlyName};

use App\MiniOS\\{$studlyName}\Livewire\\{$studlyName};
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Support\WindowConfig;

class {$studlyName}App implements DesktopApp
{
    public function id(): string
    {
        return '{$kebabName}';
    }

    public function name(): string
    {
        return '{$studlyName}';
    }

    public function icon(): string
    {
        return '{$icon}';
    }

    public function entry(): string
    {
        return '/{$kebabName}';
    }

    public function routes(): array
    {
        return [
            '/{$kebabName}',
        ];
    }

    public function isPinned(): bool
    {
        return {$pinned};
    }

    public function component(): ?string
    {
        return {$studlyName}::class;
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(900, 600)
            ->min(500, 350);
    }
}

PHP;

        File::put($manifestPath, $manifestContent);
        $this->info("Created Manifest: {$manifestPath}");

        // Create Livewire Component if not exists
        $livewireDir = "{$appDir}/Livewire";
        if (! File::isDirectory($livewireDir)) {
            File::makeDirectory($livewireDir, 0755, true);
        }

        $livewireClassPath = "{$livewireDir}/{$studlyName}.php";
        if (! File::exists($livewireClassPath)) {
            $livewireContent = <<<PHP
<?php

namespace App\MiniOS\\{$studlyName}\Livewire;

use Livewire\Component;

class {$studlyName} extends Component
{
    public function render()
    {
        return view('apps.{$kebabName}');
    }
}

PHP;
            File::put($livewireClassPath, $livewireContent);
            $this->info("Created Livewire Component: {$livewireClassPath}");
        }

        // Create View if not exists
        $viewsDir = resource_path('views/apps');
        if (! File::isDirectory($viewsDir)) {
            File::makeDirectory($viewsDir, 0755, true);
        }

        $viewPath = "{$viewsDir}/{$kebabName}.blade.php";
        if (! File::exists($viewPath)) {
            $viewContent = <<<BLADE
<div class="p-6">
    <h2 class="text-xl font-bold">{$studlyName} App</h2>
    <p class="text-sm text-neutral-500">Welcome to your new {$studlyName} MiniOS application.</p>
</div>
BLADE;
            File::put($viewPath, $viewContent);
            $this->info("Created View: {$viewPath}");
        }

        $this->newLine();
        $this->comment('Next step: Register your app in AppServiceProvider::boot():');
        $this->line("    \\Novay\\MiniOS\\Facades\\MiniOS::register(\\App\\MiniOS\\{$studlyName}\\{$studlyName}App::class);");

        return Command::SUCCESS;
    }
}
