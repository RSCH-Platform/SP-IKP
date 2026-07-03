<?php

namespace App\Updates;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

class Update_1_1_0 implements UpdaterInterface
{
    public function getDescription(): string
    {
        return 'Safe migration update: Wipe DB, import schema, hide specific migrations, run migrate & legacy data commands.';
    }

    public function run(Command $command): void
    {
        $noWipe = $command->option('no-wipe');

        if (!$noWipe) {
            $command->warn('====================================================');
            $command->warn('⚠️  CRITICAL WARNING: DATABASE WIPE INITIATED ⚠️');
            $command->warn('====================================================');
            $command->warn('This update (v1.1.0) will DROP the current database and re-import it from a schema file.');
            $command->warn('ALL EXISTING DATA WILL BE PERMANENTLY LOST!');
            $command->newLine();

            $env = app()->environment();
            $command->warn('Current Environment: ' . strtoupper($env));

            if ($env === 'production' && !$command->option('force')) {
                throw new \Exception('Aborted. You are in PRODUCTION. Use --force to bypass this check if you are absolutely sure.');
            }

            if (!$command->option('force')) {
                $confirmation = $command->ask('To proceed with WIPING the database, please type exactly: WIPE_MY_DB_NOW');

                if ($confirmation !== 'WIPE_MY_DB_NOW') {
                    throw new \Exception('Confirmation string did not match. Operation safely aborted.');
                }
            } else {
                $command->warn('Running with --force flag. Bypassing manual confirmation.');
            }

            $command->newLine();
            $command->info('Step 1: Disconnecting and wiping database...');

            $dbName = config('database.connections.mysql.database');
            $dbHost = config('database.connections.mysql.host', '127.0.0.1');
            $dbPort = config('database.connections.mysql.port', 3306);
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');

            DB::purge('mysql');

            $envVars = [];

            if ($dbPass) {
                $envVars['MYSQL_PWD'] = $dbPass;
            }

            $processWipe = Process::fromShellCommandline(
                "mysql -h {$dbHost} -P {$dbPort} -u {$dbUser} -e \"DROP DATABASE IF EXISTS `{$dbName}`; CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\""
            );

            $processWipe->setEnv($envVars);
            $processWipe->run();

            if (!$processWipe->isSuccessful()) {
                throw new \Exception('Failed to wipe DB: ' . $processWipe->getErrorOutput());
            }

            $command->info('Step 2: Importing schema (ikp_db_new.sql)...');

            $sqlPath = base_path('database/schema/ikp_db_new.sql');

            if (!File::exists($sqlPath)) {
                throw new \Exception("Schema file not found at: {$sqlPath}");
            }

            $processImport = Process::fromShellCommandline(
                "mysql -h {$dbHost} -P {$dbPort} -u {$dbUser} --max_allowed_packet=512M --net_buffer_length=16384 {$dbName} < {$sqlPath}"
            );

            $processImport->setTimeout(null);
            $processImport->setEnv($envVars);
            $processImport->run();

            if (!$processImport->isSuccessful()) {
                throw new \Exception('Failed to import schema: ' . $processImport->getErrorOutput());
            }

            DB::reconnect('mysql');
        } else {
            $command->info('Step 1 & 2 Skipped: Running update without wiping and importing the database (--no-wipe flag used).');
        }

        $command->info('Step 3: Checking cache table...');

        if (Schema::hasTable('cache')) {
            $command->line('   -> Cache table already exists, skipping this step.');
        } else {
            $command->info('   -> Cache table not found. Creating cache table directly...');

            $cacheMigrationPath = base_path('database/migrations/0001_01_01_000001_create_cache_table.php');

            if (File::exists($cacheMigrationPath)) {
                $cacheMigration = require $cacheMigrationPath;
                $cacheMigration->up();

                $command->line('   -> Cache table created.');
            } else {
                $command->warn('   -> Cache migration not found, skipping this step.');
            }
        }

        $command->info('Step 4: Hiding target migration file...');

        $targetFile = base_path('database/migrations/2026_06_30_085238_drop_investigation_columns_from_laporan_insidens.php');
        $bakFile = $targetFile . '.bak';

        if (File::exists($targetFile)) {
            File::move($targetFile, $bakFile);
            $command->line('   -> Migration file renamed to .bak');
        } else {
            $command->line('   -> Migration file not found (might already be renamed or missing).');
        }

        $command->info('Step 5: Running standard migrations...');
        $command->call('migrate', ['--force' => true]);

        $command->info('Step 6: Running legacy data migration...');
        $command->call('app:migrate-legacy-laporan-insiden-data');

        $command->info('Step 7: Restoring target migration file...');

        if (File::exists($bakFile)) {
            File::move($bakFile, $targetFile);
            $command->line('   -> Migration file restored to .php');
        }

        $command->info('All steps for v1.1.0 completed.');
    }
}
