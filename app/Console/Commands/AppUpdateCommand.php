<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update {version? : The version to update to (e.g., 1.1.0)} {--force : Force the operation to run without strict confirmations if applicable} {--no-wipe : Do not wipe the database even if the update requires it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run application update scripts for a specific version.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $version = $this->argument('version');

        if (!$version) {
            $version = $this->ask('Please enter the version you want to update to (e.g., 1.1.0)');
        }

        if (!$version) {
            $this->error('Version is required.');
            return self::FAILURE;
        }

        // Clean version string (e.g., "v1.1.0" or "1.1.0" becomes "1_1_0")
        $cleanVersion = str_replace(['v', '.'], ['', '_'], $version);
        $className = "App\\Updates\\Update_{$cleanVersion}";

        if (!class_exists($className)) {
            $this->error("Update class for version {$version} ({$className}) not found.");
            return self::FAILURE;
        }

        $this->info("Starting update process for version: {$version}");

        try {
            /** @var \App\Updates\UpdaterInterface $updater */
            $updater = new $className();
            $this->info("Description: " . $updater->getDescription());
            
            $this->newLine();
            $updater->run($this);
            $this->newLine();

            $this->info("🎉 Update to version {$version} completed successfully!");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Update failed: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
