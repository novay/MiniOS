<?php

namespace Novay\MiniOS\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'minios:install {--force : Overwrite existing published files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and publish all MiniOS resources, configuration, and migrations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing MiniOS...');

        $this->comment('Publishing MiniOS Configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'minios-config',
            '--force' => $this->option('force'),
        ]);

        $this->comment('Publishing MiniOS Migrations...');
        $this->call('vendor:publish', [
            '--tag' => 'minios-migrations',
            '--force' => $this->option('force'),
        ]);

        $this->comment('Publishing MiniOS Assets (Images & Wallpapers)...');
        $this->call('vendor:publish', [
            '--tag' => 'minios-assets',
            '--force' => $this->option('force'),
        ]);

        $this->info('MiniOS has been successfully installed!');

        return Command::SUCCESS;
    }
}
