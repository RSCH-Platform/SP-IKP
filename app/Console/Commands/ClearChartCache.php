<?php

namespace App\Console\Commands;

use App\Services\DashboardChartService;
use Illuminate\Console\Command;

class ClearChartCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:cache:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all dashboard chart caches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DashboardChartService::clearCache();

        $this->info('All chart caches have been cleared successfully.');

        return Command::SUCCESS;
    }
}
