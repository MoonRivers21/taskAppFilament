<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class FreshMigrateAndSeed extends Command
{
    protected $signature = 'migrate:fresh-seed';

    protected $description = 'Refresh database and seed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Artisan::call('migrate:fresh', ['--seed' => true]);
        $this->info('Database migrated fresh and seeded successfully.');
    }
}
