<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DeployFinalize extends Command
{
    protected $signature = 'deploy:finalize {--force : Skip the production confirmation prompt}';

    protected $description = 'Rebuild Laravel caches after uploading to a new server (run once post-deploy).';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Rebuild config/route/view caches for deployment?', true)) {
            return self::SUCCESS;
        }

        foreach (['config:clear', 'route:clear', 'view:clear', 'cache:clear'] as $cmd) {
            $this->line("  " . $cmd);
            Artisan::call($cmd);
        }

        foreach (['config:cache', 'route:cache', 'view:cache'] as $cmd) {
            $this->line("  " . $cmd);
            try {
                Artisan::call($cmd);
            } catch (\Throwable $e) {
                $this->warn("    skipped: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('Deployment caches rebuilt.');

        return self::SUCCESS;
    }
}
