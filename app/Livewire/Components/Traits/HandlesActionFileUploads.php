<?php

namespace App\Livewire\Components\Traits;

use App\Models\ProblemAction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

trait HandlesActionFileUploads
{
    public $temporaryUploadedFiles = [];
    public $uploadedFiles = [];
    public $existingActionMedia = [];

    public function handleFileUpload($files)
    {
        $this->processUploadedFiles($files);
    }

    public function updatedTemporaryUploadedFiles()
    {
        if (empty($this->temporaryUploadedFiles)) {
            return;
        }

        $this->processUploadedFiles($this->temporaryUploadedFiles);
        $this->temporaryUploadedFiles = [];
    }

    private function processUploadedFiles($files)
    {
        try {
            if (empty($files)) {
                return;
            }

            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $maxFileSize = 5120; // KB (5MB)

            foreach ($files as $file) {
                if (is_object($file) && method_exists($file, 'getMimeType')) {
                    $mimeType = $file->getMimeType();
                    if (!in_array($mimeType, $allowedMimes)) {
                        Log::warning('ProblemAnalysisManager: Invalid file type', [
                            'fileName' => $file->getClientOriginalName(),
                            'mimeType' => $mimeType,
                        ]);
                        $this->dispatch('notify-error', message: '❌ Tipe file tidak didukung: ' . $file->getClientOriginalName());
                        continue;
                    }

                    $fileSizeKB = $file->getSize() / 1024;
                    if ($fileSizeKB > $maxFileSize) {
                        Log::warning('ProblemAnalysisManager: File too large', [
                            'fileName' => $file->getClientOriginalName(),
                            'size' => $fileSizeKB . 'KB',
                        ]);
                        $this->dispatch('notify-error', message: '❌ Ukuran file terlalu besar: ' . $file->getClientOriginalName());
                        continue;
                    }

                    $disk = config('livewire.temporary_file_upload.disk', config('filesystems.default'));
                    $path = $file->store('temp/action-evidence', $disk);

                    $this->uploadedFiles[] = [
                        'name' => $file->getClientOriginalName(),
                        'size' => round($fileSizeKB, 2),
                        'type' => $mimeType,
                        'storagePath' => $path,
                        'storageDisk' => $disk,
                    ];

                    Log::info('ProblemAnalysisManager: Temporary file uploaded: ' . $file->getClientOriginalName());
                    Log::debug('ProblemAnalysisManager: Temporary upload details', [
                        'original_file_name' => $file->getClientOriginalName(),
                        'mime_type' => $mimeType,
                        'size_kb' => round($fileSizeKB, 2),
                        'temp_storage_disk' => $disk,
                        'temp_storage_path' => $path,
                    ]);
                } elseif (is_array($file) && isset($file['name'])) {
                    Log::warning('ProblemAnalysisManager: Skipping unsupported raw file array upload', [
                        'file' => $file,
                    ]);
                }
            }

            if (!empty($this->uploadedFiles)) {
                $this->dispatch('notify', message: '✅ ' . count($this->uploadedFiles) . ' file(s) siap untuk disimpan');
                Log::info('ProblemAnalysisManager: Uploaded files staged for action save', [
                    'staged_file_count' => count($this->uploadedFiles),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('ProblemAnalysisManager: Error handling file upload', [
                'error' => $e->getMessage(),
            ]);
            $this->dispatch('notify-error', message: '❌ Error: ' . $e->getMessage());
        }
    }

    public function removeUploadedFile($index)
    {
        try {
            if (isset($this->uploadedFiles[$index])) {
                $file = $this->uploadedFiles[$index];

                if (Storage::disk($file['storageDisk'] ?? config('filesystems.default'))->exists($file['storagePath'])) {
                    Storage::disk($file['storageDisk'] ?? config('filesystems.default'))->delete($file['storagePath']);
                }

                unset($this->uploadedFiles[$index]);
                $this->uploadedFiles = array_values($this->uploadedFiles);

                Log::info('ProblemAnalysisManager: Uploaded file removed from staging', [
                    'file_name' => $file['name'],
                    'storage_path' => $file['storagePath'],
                ]);

                $this->dispatch('notify', message: '✅ File dihapus dari antrian upload');
            }
        } catch (\Exception $e) {
            Log::error('ProblemAnalysisManager: Error removing file', [
                'error' => $e->getMessage(),
            ]);
            $this->dispatch('notify-error', message: '❌ Error: ' . $e->getMessage());
        }
    }

    public function deleteExistingFile($actionId, $mediaId)
    {
        try {
            $action = ProblemAction::findOrFail($actionId);
            $media = $action->media()->find($mediaId);

            if ($media) {
                $media->delete();
                Log::info('ProblemAnalysisManager: File deleted from action', [
                    'actionId' => $actionId,
                    'mediaId' => $mediaId,
                ]);
                $this->dispatch('notify', message: '✅ File dihapus dari tindakan');
                $this->loadProblems();
            }
        } catch (\Exception $e) {
            Log::error('ProblemAnalysisManager: Error deleting file', [
                'error' => $e->getMessage(),
            ]);
            $this->dispatch('notify-error', message: '❌ Error: ' . $e->getMessage());
        }
    }
}
