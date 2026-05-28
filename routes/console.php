<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('minio:check {disk? : The filesystem disk to check; defaults to the active filesystem disk} {--quick : Validate configuration only, do not attempt network connection}', function () {
    $environment = config('app.env', 'production');
    $disk = $this->argument('disk') ?: config('filesystems.default');
    $quick = $this->option('quick');

    $this->info("Environment: {$environment}");
    $this->info("Checking filesystem disk: {$disk}");

    $diskConfig = config("filesystems.disks.{$disk}");

    if (! $diskConfig) {
        $this->error("Disk '{$disk}' is not configured in config/filesystems.php.");
        return 1;
    }

    $driver = $diskConfig['driver'] ?? null;

    if ($driver === 'local') {
        $this->info("The '{$disk}' disk uses the local driver, which is expected outside production.");
    } elseif ($driver !== 's3') {
        $this->warn("The '{$disk}' disk is configured using driver '{$driver}'. MinIO requires an S3-compatible disk.");
    }

    if ($driver === 'local') {
        $this->info('Configuration check passed.');
        $this->line('Local storage path: ' . Storage::disk($disk)->path(''));
        $this->line('Quick mode: ' . ($quick ? '<fg=yellow>enabled</>' : '<fg=green>disabled</>'));
        $this->info('✅ Local storage is configured correctly.');
        return 0;
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
        return 1;
    }

    $endpoint = $diskConfig['endpoint'];
    $isMinio = str_contains($endpoint, 'minio') || str_contains($endpoint, 'localhost') || str_contains($endpoint, '127.0.0.1');

    $this->info('Configuration check passed.');
    $this->line("Endpoint: {$endpoint}");
    $this->line('MinIO-style endpoint: ' . ($isMinio ? '<fg=green>yes</>' : '<fg=yellow>unknown</>'));
    $this->line('Quick mode: ' . ($quick ? '<fg=yellow>enabled</>' : '<fg=green>disabled</>'));

    if ($quick) {
        $this->info('✅ Configuration is valid for MinIO / S3 storage.');
        return 0;
    }

    $normalizedEndpoint = trim($endpoint);

    if ($normalizedEndpoint === '') {
        $this->error('The configured endpoint URL is invalid.');
        return 1;
    }

    if (! preg_match('/^[a-z][a-z0-9+\-.]*:\/\//i', $normalizedEndpoint)) {
        $normalizedEndpoint = 'http://' . $normalizedEndpoint;
    }

    if (! filter_var($normalizedEndpoint, FILTER_VALIDATE_URL)) {
        $this->error('The configured endpoint URL is invalid.');
        return 1;
    }

    $this->info('Testing network connectivity to the MinIO / S3 endpoint...');
    $this->line('Endpoint URL: ' . $normalizedEndpoint);

    $endpointReachable = false;

    if (function_exists('curl_init')) {
        $this->line('Trying a cURL HEAD request...');
        $handle = curl_init($normalizedEndpoint);

        if ($handle !== false) {
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

            if ($errorNumber === 0 && $response !== false && $statusCode > 0) {
                $this->line('cURL HTTP status: ' . $statusCode);
                $this->info('✅ Endpoint responded to the cURL request.');
                $endpointReachable = true;
            } elseif ($errorNumber !== 0) {
                $this->warn('cURL error: ' . $errorMessage);
            } else {
                $this->warn('The endpoint did not return a valid HTTP response.');
            }
        } else {
            $this->warn('Unable to initialize cURL.');
        }
    } else {
        $this->warn('cURL extension is not available, trying a raw TCP connection instead...');
    }

    if (! $endpointReachable) {
        $parts = parse_url($normalizedEndpoint);
        $host = $parts['host'] ?? null;

        if (! is_string($host) || $host === '') {
            $this->error('The endpoint host could not be determined.');
            return 1;
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
            return 1;
        }

        fclose($socket);
        $this->info('✅ TCP connection to the endpoint succeeded.');
    }

    $filesystem = Storage::disk($disk);
    $testFileName = 'minio-check-' . uniqid() . '.txt';
    $copyFileName = 'minio-check-copy-' . uniqid() . '.txt';
    $testContent = 'MinIO access test at ' . now()->toIso8601String();
    $expectedSize = strlen($testContent);
    $created = false;
    $copied = false;

    try {
        $this->info('Testing write access by uploading a temporary file...');
        $result = $filesystem->put($testFileName, $testContent);

        if ($result !== true) {
            $this->error('❌ Failed to upload the test file to MinIO / S3.');
            Log::error('MinIO check failed during upload operation: put() returned false.', [
                'disk' => $disk,
                'endpoint' => $endpoint,
                'test_file' => $testFileName,
                'driver' => get_class($filesystem->getDriver()),
                'disk_config' => $diskConfig,
            ]);
            return 1;
        }

        $created = true;
        $this->info('Testing read access by downloading the temporary file...');
        $downloaded = $filesystem->get($testFileName);

        if ($downloaded !== $testContent) {
            $this->error('❌ Downloaded content does not match uploaded content.');
            Log::error('MinIO check failed because downloaded file content differs from uploaded file.', [
                'disk' => $disk,
                'endpoint' => $endpoint,
                'test_file' => $testFileName,
            ]);
            return 1;
        }

        $this->info('Testing metadata lookup for the uploaded file...');
        $size = $filesystem->size($testFileName);

        if ((int) $size !== $expectedSize) {
            $this->error('❌ File size metadata does not match expected content size.');
            Log::error('MinIO check failed during metadata lookup.', [
                'disk' => $disk,
                'endpoint' => $endpoint,
                'test_file' => $testFileName,
                'expected_size' => $expectedSize,
                'actual_size' => $size,
            ]);
            return 1;
        }

        $this->info('Testing file listing on the bucket root...');
        $files = $filesystem->files('');
        if (! in_array($testFileName, $files, true)) {
            $this->error('❌ Uploaded file was not found in the root file listing.');
            Log::error('MinIO check failed during file listing.', [
                'disk' => $disk,
                'endpoint' => $endpoint,
                'test_file' => $testFileName,
                'files' => $files,
            ]);
            return 1;
        }

        $this->info('Testing copy operation for the test file...');
        if ($filesystem->copy($testFileName, $copyFileName) !== true) {
            $this->error('❌ Failed to copy the test file.');
            Log::error('MinIO check failed during copy operation.', [
                'disk' => $disk,
                'endpoint' => $endpoint,
                'source' => $testFileName,
                'destination' => $copyFileName,
            ]);
            return 1;
        }

        $copied = true;
        $copyDownloaded = $filesystem->get($copyFileName);
        if ($copyDownloaded !== $testContent) {
            $this->error('❌ Copied file content does not match original content.');
            Log::error('MinIO check failed because copied file content differs from original.', [
                'disk' => $disk,
                'endpoint' => $endpoint,
                'copy_file' => $copyFileName,
            ]);
            return 1;
        }

        $this->info('Deleting copied test file...');
        if (! $filesystem->delete($copyFileName)) {
            $this->error('❌ Failed to delete the copied test file.');
            Log::error('MinIO check failed while deleting copied file.', [
                'disk' => $disk,
                'endpoint' => $endpoint,
                'copy_file' => $copyFileName,
            ]);
            return 1;
        }

        $copied = false;
        $this->info('Deleting original test file...');
        if (! $filesystem->delete($testFileName)) {
            $this->error('❌ Failed to delete the original test file.');
            Log::error('MinIO check failed while deleting original file.', [
                'disk' => $disk,
                'endpoint' => $endpoint,
                'test_file' => $testFileName,
            ]);
            return 1;
        }

        $created = false;
        $this->info('✅ MinIO / S3 upload, download, metadata, listing, copy, and delete test passed.');
        return 0;
    } catch (\Exception $exception) {
        $this->error('❌ Failed to access MinIO / S3 storage.');
        $this->error($exception->getMessage());
        Log::error('MinIO check exception thrown.', [
            'disk' => $disk,
            'endpoint' => $endpoint,
            'test_file' => $testFileName,
            'copy_file' => $copyFileName,
            'exception' => $exception->getMessage(),
            'driver' => get_class($filesystem->getDriver()),
            'disk_config' => $diskConfig,
        ]);
        $this->line('Please verify AWS_* environment variables, endpoint URL, region, bucket name, credentials, and network access.');
        return 1;
    } finally {
        if (isset($filesystem)) {
            if ($copied) {
                try {
                    $filesystem->delete($copyFileName);
                } catch (\Exception $ignored) {
                }
            }
            if ($created) {
                try {
                    $filesystem->delete($testFileName);
                } catch (\Exception $ignored) {
                }
            }
        }
    }
})->purpose('Check whether MinIO / S3 storage is configured and reachable');
