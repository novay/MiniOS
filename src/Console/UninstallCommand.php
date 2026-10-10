<?php

namespace Novay\MiniOS\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class UninstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'minios:uninstall 
                            {--force : Force the uninstallation without confirmation prompts}
                            {--drop-tables : Drop the MiniOS database tables}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstall MiniOS, remove published assets/configurations, and revert frontend integration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->warn('MiniOS Uninstaller');

        if (! $this->option('force') && ! $this->confirm('Are you sure you want to uninstall MiniOS? This will remove published MiniOS assets, configurations, and revert frontend modifications.', false)) {
            $this->info('MiniOS uninstallation cancelled.');

            return Command::SUCCESS;
        }

        $this->info('Uninstalling MiniOS...');

        $this->removePublishedFiles();
        $this->handleDatabase();
        $this->revertFrontend();
        $this->revertRoutes();
        $this->revertFortify();

        $this->info('MiniOS has been successfully uninstalled!');

        return Command::SUCCESS;
    }

    /**
     * Remove published files, configs, assets, views, and skills.
     */
    protected function removePublishedFiles(): void
    {
        $this->comment('Removing published files and directories...');

        // 1. Config
        $configPath = config_path('minios.php');
        if (File::exists($configPath)) {
            File::delete($configPath);
            $this->line('  <info>✓</info> Deleted config/minios.php');
        }

        // 2. Public Assets (Images & Wallpapers)
        $publicAssets = public_path('minios');
        if (File::isDirectory($publicAssets)) {
            File::deleteDirectory($publicAssets);
            $this->line('  <info>✓</info> Removed public/minios/');
        }

        // 3. Compiled Assets (dist)
        $publicDist = public_path('vendor/minios');
        if (File::isDirectory($publicDist)) {
            File::deleteDirectory($publicDist);
            $this->line('  <info>✓</info> Removed public/vendor/minios/');
        }

        // 4. Published Views
        $publishedViews = resource_path('views/vendor/minios');
        if (File::isDirectory($publishedViews)) {
            File::deleteDirectory($publishedViews);
            $this->line('  <info>✓</info> Removed resources/views/vendor/minios/');
        }

        // 5. Published Source Code (if any)
        $publishedJs = resource_path('js/vendor/minios');
        if (File::isDirectory($publishedJs)) {
            File::deleteDirectory($publishedJs);
            $this->line('  <info>✓</info> Removed resources/js/vendor/minios/');
        }

        $publishedCss = resource_path('css/vendor/minios');
        if (File::isDirectory($publishedCss)) {
            File::deleteDirectory($publishedCss);
            $this->line('  <info>✓</info> Removed resources/css/vendor/minios/');
        }

        // 6. Published Translations
        $publishedLang = $this->laravel->langPath('vendor/minios');
        if (File::isDirectory($publishedLang)) {
            File::deleteDirectory($publishedLang);
            $this->line('  <info>✓</info> Removed lang/vendor/minios/');
        }

        // 7. Agent Skills
        $agentSkill = base_path('.agents/skills/minios-app-development');
        if (File::isDirectory($agentSkill)) {
            File::deleteDirectory($agentSkill);
            $this->line('  <info>✓</info> Removed .agents/skills/minios-app-development/');
        }
    }

    /**
     * Handle migration files and database tables.
     */
    protected function handleDatabase(): void
    {
        $this->comment('Handling database and migration files...');

        // 1. Remove published migration files
        $migrationFiles = File::glob(database_path('migrations/*_create_os_settings_table.php'));
        foreach ($migrationFiles as $file) {
            File::delete($file);
            $this->line('  <info>✓</info> Deleted migration: '.basename($file));
        }

        // 2. Drop table if requested
        $dropTables = $this->option('drop-tables');
        if (! $dropTables && ! $this->option('force')) {
            $dropTables = $this->confirm('Do you also want to drop the MiniOS database table (`settings`)?', false);
        }

        if ($dropTables) {
            try {
                if (Schema::hasTable('settings')) {
                    Schema::dropIfExists('settings');
                    $this->line('  <info>✓</info> Dropped `settings` database table.');
                }

                if (Schema::hasTable('migrations')) {
                    DB::table('migrations')
                        ->where('migration', 'like', '%create_os_settings_table%')
                        ->delete();
                }
            } catch (\Throwable $e) {
                $this->warn('  <comment>!</comment> Failed to drop settings table: '.$e->getMessage());
            }
        }
    }

    /**
     * Revert frontend changes (vite.config.js, app.css, app.js).
     */
    protected function revertFrontend(): void
    {
        $this->comment('Reverting frontend integration...');

        $this->revertVite();
        $this->revertAppCss();
        $this->revertAppJs();
    }

    /**
     * Remove MiniOS aliases from vite.config.js.
     */
    protected function revertVite(): void
    {
        $vitePath = base_path('vite.config.js');
        if (! File::exists($vitePath)) {
            return;
        }

        $content = File::get($vitePath);
        $original = $content;

        // Remove individual minios alias lines
        $content = preg_replace('/\n?\s*[\'"]@minios[^\'"]*[\'"]:\s*path\.resolve\([^\)]+\),?/i', '', $content);

        // If alias object is now empty: alias: { \s* }
        $content = preg_replace('/alias:\s*\{\s*\},?\n?/', '', $content);

        // If resolve block is now empty: resolve: { \s* }
        $content = preg_replace('/resolve:\s*\{\s*\},?\n?/', '', $content);

        if ($content !== $original) {
            File::put($vitePath, $content);
            $this->line('  <info>✓</info> Reverted MiniOS aliases from vite.config.js.');
        }
    }

    /**
     * Remove MiniOS stylesheet imports and Blade view scanning from resources/css/app.css.
     */
    protected function revertAppCss(): void
    {
        $cssPath = resource_path('css/app.css');
        if (! File::exists($cssPath)) {
            return;
        }

        $content = File::get($cssPath);
        $original = $content;

        // Remove any @import for minios.css
        $content = preg_replace('/\n?@import\s+[\'"][^\'"]*minios\.css[\'"];?/i', '', $content);

        // Remove any @source for minios views
        $content = preg_replace('/\n?@source\s+[\'"][^\'"]*minios[^\'"]*views[^\'"]*[\'"];?/i', '', $content);

        if ($content !== $original) {
            File::put($cssPath, $content);
            $this->line('  <info>✓</info> Reverted MiniOS CSS imports and @source from resources/css/app.css.');
        }
    }

    /**
     * Remove MiniOS Alpine registration from resources/js/app.js.
     */
    protected function revertAppJs(): void
    {
        $jsPath = resource_path('js/app.js');
        if (! File::exists($jsPath)) {
            return;
        }

        $content = File::get($jsPath);
        $original = $content;

        // Remove import minios from '@minios...'
        $content = preg_replace('/\n?import\s+minios\s+from\s+[\'"][^\'"]*minios[^\'"]*[\'"];?/', '', $content);

        // Remove Alpine.data('minios', minios);
        $content = preg_replace('/\n?Alpine\.data\(\s*[\'"]minios[\'"]\s*,\s*minios\s*\);?/', '', $content);

        if ($content !== $original) {
            File::put($jsPath, $content);
            $this->line('  <info>✓</info> Reverted MiniOS registration from resources/js/app.js.');
        }
    }

    /**
     * Remove MiniOS::routes() from routes/web.php.
     */
    protected function revertRoutes(): void
    {
        $routePath = base_path('routes/web.php');
        if (! File::exists($routePath)) {
            return;
        }

        $content = File::get($routePath);
        $original = $content;

        // Remove comment and route call
        $content = preg_replace('/\n?\/\/\s*MiniOS Desktop Routes\n?/i', '', $content);
        $content = preg_replace('/\n?MiniOS::routes\(\);[^\n]*/', '', $content);

        // Remove import if MiniOS is no longer referenced in routes/web.php
        if (! str_contains($content, 'MiniOS::')) {
            $content = preg_replace('/\n?use\s+Novay\\\\MiniOS\\\\Facades\\\\MiniOS;[^\n]*/', '', $content);
        }

        if ($content !== $original) {
            File::put($routePath, trim($content)."\n");
            $this->line('  <info>✓</info> Removed MiniOS routes from routes/web.php.');
        }
    }

    /**
     * Remove MiniOS::fortify() from FortifyServiceProvider.php.
     */
    protected function revertFortify(): void
    {
        $fortifyPath = app_path('Providers/FortifyServiceProvider.php');
        if (! File::exists($fortifyPath)) {
            return;
        }

        $content = File::get($fortifyPath);
        $original = $content;

        $content = preg_replace('/\n?\s*MiniOS::fortify\(\);/', '', $content);

        if (! str_contains($content, 'MiniOS::')) {
            $content = preg_replace('/\n?use\s+Novay\\\\MiniOS\\\\Facades\\\\MiniOS;[^\n]*/', '', $content);
        }

        if ($content !== $original) {
            File::put($fortifyPath, $content);
            $this->line('  <info>✓</info> Reverted MiniOS authentication from FortifyServiceProvider.php.');
        }
    }
}
