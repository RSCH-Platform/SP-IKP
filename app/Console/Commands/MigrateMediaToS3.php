<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MigrateMediaToS3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:migrate-to-s3 
                            {--disk=s3 : The target disk to migrate to}
                            {--from=public : The source disk to migrate from}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate local media files to S3 and update the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetDiskName = $this->option('disk');
        $sourceDiskName = $this->option('from');

        $this->info("Starting migration from disk '{$sourceDiskName}' to '{$targetDiskName}'...");

        $targetDisk = Storage::disk($targetDiskName);
        $sourceDisk = Storage::disk($sourceDiskName);

        $mediaItems = Media::where('disk', $sourceDiskName)->orWhere('disk', 'local')->get();

        if ($mediaItems->isEmpty()) {
            $this->info("No media items found on disk '{$sourceDiskName}' or 'local'.");
            return 0;
        }

        $bar = $this->output->createProgressBar($mediaItems->count());
        $bar->start();

        $errors = 0;
        $success = 0;

        foreach ($mediaItems as $media) {
            try {
                // Get the old disk instance (could be 'public' or 'local')
                $currentDisk = Storage::disk($media->disk);
                
                // Get the relative path of the original file
                $path = $media->getPathRelativeToRoot();
                
                // Copy the main file if it exists
                if ($currentDisk->exists($path)) {
                    $stream = $currentDisk->readStream($path);
                    if ($stream) {
                        $targetDisk->writeStream($path, $stream);
                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    } else {
                        $targetDisk->put($path, $currentDisk->get($path));
                    }
                } else {
                    $this->warn("\nFile not found on source disk: {$path}");
                }

                // Copy conversions directory if it exists
                $pathGenerator = \Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory::create($media);
                $conversionsPath = rtrim($pathGenerator->getPathForConversions($media), '/');
                
                // We check if it's a directory and has files
                if ($conversionsPath && $currentDisk->exists($conversionsPath)) {
                    // Try to get all files in the conversions path
                    try {
                        $conversionFiles = $currentDisk->allFiles($conversionsPath);
                        foreach ($conversionFiles as $cFile) {
                            $stream = $currentDisk->readStream($cFile);
                            if ($stream) {
                                $targetDisk->writeStream($cFile, $stream);
                                if (is_resource($stream)) {
                                    fclose($stream);
                                }
                            } else {
                                $targetDisk->put($cFile, $currentDisk->get($cFile));
                            }
                        }
                    } catch (\Exception $e) {
                        // ignore if not a directory
                    }
                }

                // Copy responsive images directory if it exists
                $responsivePath = rtrim($pathGenerator->getPathForResponsiveImages($media), '/');
                if ($responsivePath && $currentDisk->exists($responsivePath)) {
                    try {
                        $responsiveFiles = $currentDisk->allFiles($responsivePath);
                        foreach ($responsiveFiles as $rFile) {
                            $stream = $currentDisk->readStream($rFile);
                            if ($stream) {
                                $targetDisk->writeStream($rFile, $stream);
                                if (is_resource($stream)) {
                                    fclose($stream);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // ignore
                    }
                }

                // Update database
                $media->disk = $targetDiskName;
                $media->conversions_disk = $targetDiskName;
                $media->saveQuietly(); // Use saveQuietly to prevent triggering any observer events

                $success++;
            } catch (\Exception $e) {
                $this->error("\nFailed to migrate Media ID {$media->id}: " . $e->getMessage());
                $errors++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Migration completed!");
        $this->info("Success: {$success}");
        if ($errors > 0) {
            $this->error("Failed: {$errors}");
        }

        return 0;
    }

}
