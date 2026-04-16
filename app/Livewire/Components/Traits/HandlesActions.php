<?php

namespace App\Livewire\Components\Traits;

use App\Models\ProblemAction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait HandlesActions
{
    public $actionFormData = [];

    public function addAction($problemId = null)
    {
        if ($problemId) {
            $this->editingProblemId = $problemId;
        }

        $this->editingItemId = null;
        $this->editingItemType = 'action';
        $this->actionFormData = [
            'action_text' => '',
            'responsible_person' => '',
            'deadline' => '',
            'status' => 'pending',
        ];
        $this->temporaryUploadedFiles = [];
        $this->uploadedFiles = [];
        $this->existingActionMedia = [];

        Log::info('ProblemAnalysisManager: addAction called', [
            'problemId' => $problemId,
            'editingProblemId' => $this->editingProblemId,
        ]);
    }

    public function saveAction()
    {
        try {
            Log::info('ProblemAnalysisManager: saveAction called', [
                'editingItemId' => $this->editingItemId,
                'editingProblemId' => $this->editingProblemId,
                'formData' => $this->actionFormData,
                'uploadedFilesCount' => count($this->uploadedFiles),
            ]);

            if (empty($this->actionFormData['action_text'])) {
                Log::warning('ProblemAnalysisManager: Action text is empty');
                $this->dispatch('notify-error', message: '❌ Deskripsi tindakan tidak boleh kosong');
                return;
            }

            if (empty($this->editingProblemId)) {
                Log::error('ProblemAnalysisManager: editingProblemId is empty for action!');
                $this->dispatch('notify-error', message: '❌ Problem ID tidak ditemukan');
                return;
            }

            $data = [
                'action_text' => $this->actionFormData['action_text'],
                'responsible_person' => $this->actionFormData['responsible_person'] ?? null,
                'status' => $this->actionFormData['status'] ?? 'pending',
            ];

            if (!empty($this->actionFormData['deadline'])) {
                $data['deadline'] = Carbon::createFromFormat('Y-m-d', $this->actionFormData['deadline']);
            }

            if ($this->editingItemId) {
                $action = ProblemAction::findOrFail($this->editingItemId);
                $action->update($data);
                $message = '✅ Tindakan berhasil diperbarui';
                Log::info('ProblemAnalysisManager: Action updated', ['id' => $this->editingItemId]);
            } else {
                $data['problem_id'] = $this->editingProblemId;
                $action = ProblemAction::create($data);
                $message = '✅ Tindakan berhasil ditambahkan';
                Log::info('ProblemAnalysisManager: Action created', ['id' => $action->id]);
            }

            if (!empty($this->uploadedFiles)) {
                foreach ($this->uploadedFiles as $file) {
                    try {
                        $action->addMedia($file['path'])
                            ->preservingOriginal()
                            ->toMediaCollection('action_evidence');

                        Log::info('ProblemAnalysisManager: File uploaded to action', [
                            'actionId' => $action->id,
                            'fileName' => $file['name'],
                        ]);
                    } catch (\Exception $fileE) {
                        Log::error('ProblemAnalysisManager: Error uploading file', [
                            'error' => $fileE->getMessage(),
                            'fileName' => $file['name'],
                        ]);
                    }
                }
                $this->uploadedFiles = [];
            }

            $this->loadProblems();
            $this->resetForms();
            $this->dispatch('notify', message: $message);
        } catch (\Exception $e) {
            Log::error('ProblemAnalysisManager: Error saving action', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatch('notify-error', message: '❌ Error: ' . $e->getMessage());
        }
    }

    public function editAction($actionId)
    {
        try {
            $action = ProblemAction::findOrFail($actionId);

            $this->editingItemId = $actionId;
            $this->editingItemType = 'action';
            $this->editingProblemId = $action->problem_id;
            $this->actionFormData = [
                'action_text' => $action->action_text,
                'responsible_person' => $action->responsible_person,
                'deadline' => $action->deadline?->format('Y-m-d') ?? '',
                'status' => $action->status,
            ];
            $this->temporaryUploadedFiles = [];
            $this->uploadedFiles = [];
            $this->existingActionMedia = $action->getMedia('action_evidence')->map(fn($file) => [
                'id' => $file->id,
                'name' => $file->file_name,
                'url' => $file->getUrl(),
                'mime' => $file->mime_type,
            ])->toArray();

            Log::info('ProblemAnalysisManager: Loaded action for edit', [
                'id' => $actionId,
                'problemId' => $action->problem_id,
                'existingMediaCount' => count($this->existingActionMedia),
            ]);

            $this->dispatch('notify', message: '✅ Form edit tindakan berhasil dimuat');
        } catch (\Exception $e) {
            Log::error('ProblemAnalysisManager: Error loading action', [
                'id' => $actionId,
                'error' => $e->getMessage(),
            ]);
            $this->dispatch('notify-error', message: '❌ Error: ' . $e->getMessage());
        }
    }

    public function deleteAction($actionId)
    {
        try {
            $action = ProblemAction::findOrFail($actionId);
            $action->clearMediaCollection('action_evidence');
            ProblemAction::destroy($actionId);
            $this->loadProblems();

            Log::info('ProblemAnalysisManager: Action deleted (with files)', ['id' => $actionId]);
            $this->dispatch('notify', message: '✅ Tindakan berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('ProblemAnalysisManager: Error deleting action', [
                'id' => $actionId,
                'error' => $e->getMessage(),
            ]);
            $this->dispatch('notify-error', message: '❌ Error: ' . $e->getMessage());
        }
    }
}
