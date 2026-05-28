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
            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('❌ Failed to connect to MinIO / S3 storage.');
            $this->error($exception->getMessage());
            $this->line('Please verify AWS_* environment variables, endpoint URL, region, bucket name, and network access.');
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

        if (function_exists('curl_init')) {
            $curlSuccess = $this->probeWithCurl($normalizedEndpoint);

            if ($curlSuccess) {
                return true;
            }

            $this->warn('cURL probe failed, trying a raw TCP connection instead...');
        } else {
            $this->warn('cURL extension is not available, trying a raw TCP connection instead...');
        }

        return $this->probeWithSocket($normalizedEndpoint);
    }

    private function probeWithCurl(string $endpoint): bool
    {
        $this->line('Trying a cURL HEAD request...');

        $handle = curl_init($endpoint);

        if ($handle === false) {
            $this->warn('Unable to initialize cURL.');
            return false;
        }

        curl_setopt_array($handle, [
            CURLOPT_NOBODY => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'ikp-minio-check/1.0',
        ]);

        $response = curl_exec($handle);
        $errorNumber = curl_errno($handle);
        $errorMessage = curl_error($handle);
        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        if ($errorNumber !== 0) {
            $this->warn('cURL error: ' . $errorMessage);
            return false;
        }

        $this->line('cURL HTTP status: ' . $statusCode);

        if ($response === false || $statusCode === 0) {
            $this->warn('The endpoint did not return a valid HTTP response.');
            return false;
        }

        $this->info('✅ Endpoint responded to the cURL request.');
        return true;
    }

    private function probeWithSocket(string $endpoint): bool
    {
        $parts = parse_url($endpoint);
        $host = $parts['host'] ?? null;

        if (! is_string($host) || $host === '') {
            $this->error('The endpoint host could not be determined.');
            return false;
        }

        $scheme = strtolower($parts['scheme'] ?? 'http');
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
        $transport = $scheme === 'https' ? 'ssl' : 'tcp';

        $this->line("Trying a {$transport} connection to {$host}:{$port}...");

        $errorNumber = 0;
        $errorMessage = '';
        $socket = @stream_socket_client(
            "{$transport}://{$host}:{$port}",
            $errorNumber,
            $errorMessage,
            5
        );

        if (! is_resource($socket)) {
            $this->error('Socket connection failed: ' . trim($errorMessage) . " ({$errorNumber})");
            return false;
        }

        fclose($socket);

        $this->info('✅ TCP connection to the endpoint succeeded.');
        return true;
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
