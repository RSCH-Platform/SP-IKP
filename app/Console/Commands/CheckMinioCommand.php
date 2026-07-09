<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use Throwable;

class CheckMinioCommand extends Command
{
    protected $signature = 'minio:check {disk? : The filesystem disk to check; defaults to the active filesystem disk} {--quick : Validate configuration only, do not attempt network connection}';

    protected $description = 'Check whether the active storage disk is configured correctly and reachable.';

    public function handle(): int
    {
        $environment = config('app.env', 'production');
        $disk = $this->argument('disk') ?: config('filesystems.default');
        $quick = $this->option('quick');

        $this->info("Environment: {$environment}");
        $this->info("Checking filesystem disk: {$disk}");

        $diskConfig = Config::get("filesystems.disks.{$disk}");

        if (! $diskConfig) {
            $this->error("Disk '{$disk}' is not configured in config/filesystems.php.");
            return self::FAILURE;
        }

        $driver = $diskConfig['driver'] ?? null;

        if ($driver === 'local') {
            $this->info("The '{$disk}' disk uses the local driver, which is expected outside production.");
        } elseif ($driver !== 's3') {
            $this->warn("The '{$disk}' disk is configured using driver '{$driver}'. MinIO requires an S3-compatible disk.");
        }

        $this->info('Configuration check passed.');

        if ($driver === 'local') {
            $this->line('Local storage path: ' . Storage::disk($disk)->path(''));
            $this->line('Quick mode: ' . ($quick ? '<fg=yellow>enabled</>' : '<fg=green>disabled</>'));
            $this->info('✅ Local storage is configured correctly.');
            return self::SUCCESS;
        }

        $required = [
            'key' => 'AWS_ACCESS_KEY_ID',
            'secret' => 'AWS_SECRET_ACCESS_KEY',
            'region' => 'AWS_DEFAULT_REGION',
            'bucket' => 'AWS_BUCKET',
            'endpoint' => 'AWS_ENDPOINT',
        ];

        $missing = [];

        foreach ($required as $configKey => $envKey) {
            if (! ($diskConfig[$configKey] ?? null)) {
                $missing[] = $envKey;
            }
        }

        if (! empty($missing)) {
            $this->error('Missing required MinIO/S3 configuration values:');
            foreach ($missing as $envKey) {
                $this->line("  - {$envKey}");
            }
            $this->line('Set them in your .env file or filesystem configuration, then try again.');
            return self::FAILURE;
        }

        $endpoint = $diskConfig['endpoint'];
        $isMinio = str_contains($endpoint, 'minio') || str_contains($endpoint, 'localhost') || str_contains($endpoint, '127.0.0.1');

        $this->line("Endpoint: {$endpoint}");
        $this->line('MinIO-style endpoint: ' . ($isMinio ? '<fg=green>yes</>' : '<fg=yellow>unknown</>'));
        $this->line('Quick mode: ' . ($quick ? '<fg=yellow>enabled</>' : '<fg=green>disabled</>'));

        if ($quick) {
            $this->info('✅ Configuration is valid for MinIO / S3 storage.');
            return self::SUCCESS;
        }

        return $this->testConnection($disk, $diskConfig['endpoint']);
    }

    private function testConnection(string $disk, string $endpoint): int
    {
        $this->info('Testing network connectivity to the MinIO / S3 endpoint...');

        if (! $this->probeEndpoint($endpoint)) {
            return self::FAILURE;
        }

        $this->info('Testing filesystem driver access...');

        try {
            /** @var FilesystemAdapter $filesystem  */
            $filesystem = Storage::disk($disk);
            $driver = $filesystem->getDriver();

            if (method_exists($driver, 'listContents')) {
                // Flysystem/S3 can return a lazy listing, so force iteration to
                // ensure the client actually performs a network request.
                foreach ($driver->listContents('/', false) as $_item) {
                    break;
                }
            } else {
                $filesystem->exists('');
            }

            $this->info('✅ MinIO / S3 connection is working.');
            
            return $this->testFileOperations($filesystem, $disk);
        } catch (Throwable $exception) {
            $this->error('❌ Failed to connect to MinIO / S3 storage.');
            $this->error($exception->getMessage());
            $this->line('Please verify AWS_* environment variables, endpoint URL, region, bucket name, and network access.');
            return self::FAILURE;
        }
    }

    private function testFileOperations(FilesystemAdapter $filesystem, string $disk): int
    {
        $this->info("\n--- Testing File Operations ---");
        
        $testFile = 'ikp-minio-test-' . time() . '.txt';
        $content = 'MinIO / S3 test file created at ' . now()->toDateTimeString();

        try {
            $this->line("1. Uploading test file [{$testFile}]...");
            $filesystem->put($testFile, $content);
            
            $this->line("2. Verifying file exists...");
            if (!$filesystem->exists($testFile)) {
                $this->error("❌ File was not found after upload.");
                return self::FAILURE;
            }
            
            $this->line("3. Reading file content...");
            $readContent = $filesystem->get($testFile);
            if ($readContent !== $content) {
                $this->error("❌ File content mismatch.");
            }
            
            $this->line("4. Testing URL generation...");
            try {
                $publicUrl = $filesystem->url($testFile);
                $this->line("   - Public URL: <fg=cyan>{$publicUrl}</>");
            } catch (Throwable $e) {
                $this->warn("   - Public URL failed: " . $e->getMessage());
            }

            try {
                $tempUrl = $filesystem->temporaryUrl($testFile, now()->addMinutes(5));
                $this->line("   - Temporary URL: <fg=cyan>{$tempUrl}</>");
                
                $parsedTemp = parse_url($tempUrl);
                $tempHost = $parsedTemp['host'] ?? '';
                
                if (in_array(strtolower($tempHost), ['127.0.0.1', 'localhost', 'minio'])) {
                    $this->warn("   ⚠️  WARNING: Temporary URL uses internal hostname ('{$tempHost}').");
                    $this->warn("   ⚠️  If accessed from outside Docker/Server, previews will fail!");
                    $this->warn("   ⚠️  Fix by setting 'temporary_url' => env('AWS_URL') in filesystems.php.");
                }
            } catch (Throwable $e) {
                $this->warn("   - Temporary URL generation failed: " . $e->getMessage());
            }

            $this->line("5. Deleting test file...");
            $filesystem->delete($testFile);
            
            if ($filesystem->exists($testFile)) {
                $this->warn("⚠️  Test file was not deleted successfully.");
            }
            
            $this->info("\n✅ All file operations completed successfully.");
            return self::SUCCESS;
            
        } catch (Throwable $exception) {
            $this->error('❌ File operations failed.');
            $this->error($exception->getMessage());
            
            try {
                if ($filesystem->exists($testFile)) {
                    $filesystem->delete($testFile);
                }
            } catch (Throwable $e) {}
            
            return self::FAILURE;
        }
    }

    private function probeEndpoint(string $endpoint): bool
    {
        $normalizedEndpoint = $this->normalizeEndpoint($endpoint);

        if ($normalizedEndpoint === null) {
            $this->error('The configured endpoint URL is invalid.');
            return false;
        }

        $this->line('Endpoint URL: ' . $normalizedEndpoint);
        $this->line('Trying HTTP HEAD request...');

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->withoutVerifying()
                ->withOptions(['allow_redirects' => false])
                ->head($normalizedEndpoint);

            // MinIO/S3 root often returns 403, 400, or 405 for HEAD, which implies the server is running.
            if ($response->successful() || $response->status() >= 400) {
                $this->info('✅ Endpoint responded to the HTTP request (Status: ' . $response->status() . ').');
                return true;
            }

            $this->warn('The endpoint returned an unexpected status: ' . $response->status());
        } catch (\Throwable $exception) {
            $this->warn('HTTP request failed: ' . $exception->getMessage());
        }

        return false;
    }

    private function normalizeEndpoint(string $endpoint): ?string
    {
        $endpoint = trim($endpoint);

        if ($endpoint === '') {
            return null;
        }

        if (! preg_match('/^[a-z][a-z0-9+\-.]*:\/\//i', $endpoint)) {
            $endpoint = 'http://' . $endpoint;
        }

        return filter_var($endpoint, FILTER_VALIDATE_URL) ? $endpoint : null;
    }
}
