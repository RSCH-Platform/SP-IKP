<?php

namespace App\Livewire\Components;

use App\Models\IncidentProblem;
use App\Models\ProblemAction;
use App\Models\ProblemContributor;
use App\Models\ProblemContributorCategory;
use App\Models\ProblemContributorComponent;
use App\Models\ProblemContributorSubComponent;
use App\Models\ProblemContributorDescription;
use App\Models\ProblemRecommendation;
use App\Models\ProblemWhy;
use Carbon\Carbon;
use Livewire\Component;

class ProblemAnalysisManager extends Component
{
    public $recordId;
    public $problems = [];
    public $contributor_categories = [];
    public $contributor_components = [];
    public $contributor_sub_components = [];
    
    // Modal and form state
    public $activeTab = 'whys'; // whys, contributors, recommendations, actions
    public $editingProblemId = null;
    public $showModal = false; // Keep for backward compatibility, but not used
    public $modalMode = 'view'; // view, add, edit
    
    // Form fields
    public $whyFormData = [];
    public $contributorFormData = [];
    public $recommendationFormData = [];
    public $actionFormData = [];
    public $uploadedFiles = []; // Track temporary uploaded files
    
    // For editing
    public $editingItemId = null;
    public $editingItemType = null;
    
    public function mount($recordId = null)
    {
        $this->recordId = $recordId ?? request()->route('record');
        $this->loadProblems();
        $this->loadCategories();
    }

    public function loadProblems()
    {
        if (!$this->recordId) {
            return;
        }

        $incident = \App\Models\LaporanInsiden::with([
            'problems.whys',
            'problems.contributors.category',
            'problems.contributors.component',
            'problems.contributors.subComponent',
            'problems.recommendations',
            'problems.actions'
        ])->find($this->recordId);

        if ($incident) {
            $this->problems = $incident->problems->map(function ($problem) {
                return [
                    'id' => $problem->id,
                    'problem_type' => $problem->problem_type,
                    'problem_description' => $problem->problem_description,
                    'whys' => $problem->whys->sortBy('why_level')->map(fn($w) => [
                        'id' => $w->id,
                        'why_level' => $w->why_level,
                        'problem_statement' => $w->problem_statement,
                    ])->values()->toArray(),
                    'contributors' => $problem->contributors->map(fn($c) => [
                        'id' => $c->id,
                        'category_id' => $c->category_id,
                        'category_name' => $c->category?->name,
                        'component_id' => $c->component_id,
                        'component_name' => $c->component?->name,
                        'sub_component_id' => $c->sub_component_id,
                        'sub_component_name' => $c->subComponent?->name,
                        'description' => $c->description,
                    ])->toArray(),
                    'recommendations' => $problem->recommendations->map(fn($r) => [
                        'id' => $r->id,
                        'recommendation_text' => $r->recommendation_text,
                        'priority' => $r->priority,
                    ])->toArray(),
                    'actions' => $problem->actions->map(fn($a) => [
                        'id' => $a->id,
                        'action_text' => $a->action_text,
                        'responsible_person' => $a->responsible_person,
                        'deadline' => $a->deadline?->format('Y-m-d'),
                        'status' => $a->status,
                        'media' => $a->getMedia('action_evidence')->map(fn($m) => [
                            'id' => $m->id,
                            'name' => $m->file_name,
                            'url' => $m->getUrl(),
                            'mime' => $m->mime_type,
                        ])->toArray(),
                    ])->toArray(),
                ];
            })->toArray();
        }
    }

    public function loadCategories()
    {
        $this->contributor_categories = ProblemContributorCategory::with(['components.subComponents'])
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function onCategoryChange($categoryId)
    {
        $this->contributorFormData['category_id'] = $categoryId;
        $this->contributorFormData['component_id'] = null;
        $this->contributorFormData['sub_component_id'] = null;
        
        if ($categoryId) {
            $this->contributor_components = ProblemContributorComponent::where('category_id', $categoryId)
                ->orderBy('name')
                ->get()
                ->toArray();
        } else {
            $this->contributor_components = [];
        }
        
        $this->contributor_sub_components = [];
    }

    public function onComponentChange($componentId)
    {
        $this->contributorFormData['component_id'] = $componentId;
        $this->contributorFormData['sub_component_id'] = null;
        
        if ($componentId) {
            $this->contributor_sub_components = ProblemContributorSubComponent::where('component_id', $componentId)
                ->orderBy('name')
                ->get()
                ->toArray();
        } else {
            $this->contributor_sub_components = [];
        }
    }

    public function onSubComponentChange($subComponentId)
    {
        $this->contributorFormData['sub_component_id'] = $subComponentId;
        
        if ($subComponentId) {
            $descriptions = ProblemContributorDescription::where('sub_component_id', $subComponentId)
                ->orderBy('id')
                ->pluck('description')
                ->toArray();
            
            if (!empty($descriptions)) {
                $autoFilled = implode("\n", array_map(fn($desc) => "• {$desc}", $descriptions));
                $this->contributorFormData['description'] = $autoFilled;
            }
        }
    }

    public function updatedContributorFormDataCategoryId()
    {
        // Keep for backward compatibility, but onCategoryChange is preferred
        $this->onCategoryChange($this->contributorFormData['category_id']);
    }

    public function updatedContributorFormDataComponentId()
    {
        // Keep for backward compatibility, but onComponentChange is preferred
        $this->onComponentChange($this->contributorFormData['component_id']);
    }

    public function updatedContributorFormDataSubComponentId()
    {
        // Keep for backward compatibility, but onSubComponentChange is preferred
        $this->onSubComponentChange($this->contributorFormData['sub_component_id']);
    }

    public function openProblem($problemId)
    {
        $this->editingProblemId = $problemId;
        $this->activeTab = 'whys';
        $this->showModal = true;
        $this->modalMode = 'view';
    }

    public function closeProblem()
    {
        $this->showModal = false;
        $this->editingProblemId = null;
        $this->resetForms();
    }

    public function addWhy($problemId = null)
    {
        if ($problemId) {
            $this->editingProblemId = $problemId;
        }
        $this->editingItemId = null;
        $this->editingItemType = 'why';
        $this->whyFormData = ['problem_statement' => ''];
    }

    public function saveWhy()
    {
        try {
            if ($this->editingItemId) {
                $why = ProblemWhy::findOrFail($this->editingItemId);
                $why->update(['problem_statement' => $this->whyFormData['problem_statement']]);
            } else {
                ProblemWhy::create([
                    'problem_id' => $this->editingProblemId,
                    'problem_statement' => $this->whyFormData['problem_statement'],
                ]);
            }
            
            $this->loadProblems();
            $this->resetForms();
            $this->dispatch('notify', message: 'WHY berhasil disimpan');
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function deleteWhy($whyId)
    {
        try {
            ProblemWhy::destroy($whyId);
            $this->loadProblems();
            $this->dispatch('notify', message: 'WHY berhasil dihapus');
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function editWhy($whyId)
    {
        $why = ProblemWhy::findOrFail($whyId);
        $this->editingItemId = $whyId;
        $this->editingItemType = 'why';
        $this->editingProblemId = $why->problem_id;
        $this->whyFormData = ['problem_statement' => $why->problem_statement];
    }

    private function resetForms()
    {
        $this->whyFormData = [];
        $this->contributorFormData = [];
        $this->recommendationFormData = [];
        $this->actionFormData = [];
        $this->editingItemId = null;
        $this->editingItemType = null;
    }

    public function resetForm()
    {
        $this->resetForms();
    }

    // ======== CONTRIBUTOR METHODS ========
    public function addContributor($problemId = null)
    {
        if ($problemId) {
            $this->editingProblemId = $problemId;
        }
        $this->editingItemId = null;
        $this->editingItemType = 'contributor';
        $this->contributorFormData = [
            'category_id' => null, 
            'component_id' => null,
            'sub_component_id' => null,
            'description' => ''
        ];
        $this->contributor_components = [];
        $this->contributor_sub_components = [];
    }

    public function saveContributor()
    {
        try {
            \Log::info('ProblemAnalysisManager: saveContributor called', [
                'editingProblemId' => $this->editingProblemId,
                'editingItemId' => $this->editingItemId,
                'contributorFormData' => $this->contributorFormData,
            ]);

            // Validation
            if (empty($this->contributorFormData['category_id'])) {
                \Log::warning('ProblemAnalysisManager: Category ID is empty');
                $this->dispatch('notify-error', message: '❌ Kategori faktor harus diisi');
                return;
            }
            if (empty($this->contributorFormData['component_id'])) {
                \Log::warning('ProblemAnalysisManager: Component ID is empty');
                $this->dispatch('notify-error', message: '❌ Komponen harus diisi');
                return;
            }
            if (empty($this->contributorFormData['description'])) {
                \Log::warning('ProblemAnalysisManager: Description is empty');
                $this->dispatch('notify-error', message: '❌ Deskripsi faktor harus diisi');
                return;
            }

            $data = [
                'category_id' => $this->contributorFormData['category_id'],
                'component_id' => $this->contributorFormData['component_id'],
                'sub_component_id' => $this->contributorFormData['sub_component_id'] ?? null,
                'description' => $this->contributorFormData['description'],
            ];

            \Log::info('ProblemAnalysisManager: About to save contributor', [
                'data' => $data,
                'editingItemId' => $this->editingItemId,
            ]);

            if ($this->editingItemId) {
                // Update existing contributor
                $contributor = ProblemContributor::findOrFail($this->editingItemId);
                $contributor->update($data);
                $message = '✅ Faktor berhasil diperbarui';
                \Log::info('ProblemAnalysisManager: Contributor updated', ['id' => $this->editingItemId]);
            } else {
                // Create new contributor
                if (empty($this->editingProblemId)) {
                    \Log::error('ProblemAnalysisManager: editingProblemId is empty!');
                    $this->dispatch('notify-error', message: '❌ Problem ID tidak ditemukan');
                    return;
                }
                
                $data['problem_id'] = $this->editingProblemId;
                $created = ProblemContributor::create($data);
                $message = '✅ Faktor berhasil ditambahkan';
                \Log::info('ProblemAnalysisManager: Contributor created', ['id' => $created->id]);
            }
            
            $this->loadProblems();
            $this->resetForms();
            $this->dispatch('notify', message: $message);
        } catch (\Exception $e) {
            \Log::error('ProblemAnalysisManager: Error saving contributor', [
                'error' => $e->getMessage(),
                'data' => $this->contributorFormData,
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatch('notify-error', message: '❌ Error: ' . $e->getMessage());
        }
    }

    public function editContributor($contributorId)
    {
        try {
            $contributor = ProblemContributor::findOrFail($contributorId);
            $this->editingItemId = $contributorId;
            $this->editingItemType = 'contributor';
            $this->editingProblemId = $contributor->problem_id;
            $this->contributorFormData = [
                'category_id' => $contributor->category_id,
                'component_id' => $contributor->component_id,
                'sub_component_id' => $contributor->sub_component_id,
                'description' => $contributor->description,
            ];
            
            // Load components for the selected category
            if ($contributor->category_id) {
                $this->contributor_components = ProblemContributorComponent::where('category_id', $contributor->category_id)
                    ->orderBy('name')
                    ->get()
                    ->toArray();
            }
            
            // Load sub_components for the selected component
            if ($contributor->component_id) {
                $this->contributor_sub_components = ProblemContributorSubComponent::where('component_id', $contributor->component_id)
                    ->orderBy('name')
                    ->get()
                    ->toArray();
            }
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function deleteContributor($contributorId)
    {
        try {
            ProblemContributor::destroy($contributorId);
            $this->loadProblems();
            $this->dispatch('notify', message: 'Faktor berhasil dihapus');
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: 'Error: ' . $e->getMessage());
        }
    }

    // ======== RECOMMENDATION METHODS ========
    public function addRecommendation($problemId = null)
    {
        if ($problemId) {
            $this->editingProblemId = $problemId;
        }
        $this->editingItemId = null;
        $this->editingItemType = 'recommendation';
        $this->recommendationFormData = ['recommendation_text' => '', 'priority' => 'medium'];
    }

    public function saveRecommendation()
    {
        try {
            if (empty($this->recommendationFormData['recommendation_text'])) {
                $this->dispatch('notify-error', message: 'Rekomendasi tidak boleh kosong');
                return;
            }

            $data = [
                'recommendation_text' => $this->recommendationFormData['recommendation_text'],
                'priority' => $this->recommendationFormData['priority'] ?? 'medium',
            ];

            if ($this->editingItemId) {
                // Update existing recommendation
                $recommendation = ProblemRecommendation::findOrFail($this->editingItemId);
                $recommendation->update($data);
                $message = 'Rekomendasi berhasil diperbarui';
            } else {
                // Create new recommendation
                $data['problem_id'] = $this->editingProblemId;
                ProblemRecommendation::create($data);
                $message = 'Rekomendasi berhasil ditambahkan';
            }
            
            $this->loadProblems();
            $this->resetForms();
            $this->dispatch('notify', message: $message);
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function editRecommendation($recommendationId)
    {
        try {
            $recommendation = ProblemRecommendation::findOrFail($recommendationId);
            $this->editingItemId = $recommendationId;
            $this->editingItemType = 'recommendation';
            $this->editingProblemId = $recommendation->problem_id;
            $this->recommendationFormData = [
                'recommendation_text' => $recommendation->recommendation_text,
                'priority' => $recommendation->priority,
            ];
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function deleteRecommendation($recommendationId)
    {
        try {
            ProblemRecommendation::destroy($recommendationId);
            $this->loadProblems();
            $this->dispatch('notify', message: 'Rekomendasi berhasil dihapus');
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: 'Error: ' . $e->getMessage());
        }
    }

    // ======== ACTION METHODS ========
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
            'status' => 'pending'
        ];
        $this->uploadedFiles = []; // Reset uploaded files
        
        \Log::info('ProblemAnalysisManager: addAction called', [
            'problemId' => $problemId,
            'editingProblemId' => $this->editingProblemId
        ]);
    }

    public function saveAction()
    {
        try {
            \Log::info('ProblemAnalysisManager: saveAction called', [
                'editingItemId' => $this->editingItemId,
                'editingProblemId' => $this->editingProblemId,
                'formData' => $this->actionFormData,
                'uploadedFilesCount' => count($this->uploadedFiles),
            ]);

            // Validation
            if (empty($this->actionFormData['action_text'])) {
                \Log::warning('ProblemAnalysisManager: Action text is empty');
                $this->dispatch('notify-error', message: '❌ Deskripsi tindakan tidak boleh kosong');
                return;
            }

            if (empty($this->editingProblemId)) {
                \Log::error('ProblemAnalysisManager: editingProblemId is empty for action!');
                $this->dispatch('notify-error', message: '❌ Problem ID tidak ditemukan');
                return;
            }

            $data = [
                'action_text' => $this->actionFormData['action_text'],
                'responsible_person' => $this->actionFormData['responsible_person'] ?? null,
                'status' => $this->actionFormData['status'] ?? 'pending',
            ];

            // Handle deadline - convert to proper date format
            if (!empty($this->actionFormData['deadline'])) {
                $data['deadline'] = Carbon::createFromFormat('Y-m-d', $this->actionFormData['deadline']);
            }

            if ($this->editingItemId) {
                // Update existing action
                $action = ProblemAction::findOrFail($this->editingItemId);
                $action->update($data);
                $message = '✅ Tindakan berhasil diperbarui';
                \Log::info('ProblemAnalysisManager: Action updated', ['id' => $this->editingItemId]);
            } else {
                // Create new action
                $data['problem_id'] = $this->editingProblemId;
                $action = ProblemAction::create($data);
                $message = '✅ Tindakan berhasil ditambahkan';
                \Log::info('ProblemAnalysisManager: Action created', ['id' => $action->id]);
            }
            
            // Handle file uploads if any
            if (!empty($this->uploadedFiles)) {
                foreach ($this->uploadedFiles as $file) {
                    try {
                        $action->addMedia($file['path'])
                            ->preservingOriginal()
                            ->toMediaCollection('action_evidence');
                        
                        \Log::info('ProblemAnalysisManager: File uploaded to action', [
                            'actionId' => $action->id,
                            'fileName' => $file['name'],
                        ]);
                    } catch (\Exception $fileE) {
                        \Log::error('ProblemAnalysisManager: Error uploading file', [
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
            \Log::error('ProblemAnalysisManager: Error saving action', [
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
            $this->uploadedFiles = []; // Reset for edit
            
            \Log::info('ProblemAnalysisManager: Loaded action for edit', [
                'id' => $actionId,
                'problemId' => $action->problem_id,
            ]);
            
            $this->dispatch('notify', message: '✅ Form edit tindakan berhasil dimuat');
        } catch (\Exception $e) {
            \Log::error('ProblemAnalysisManager: Error loading action', [
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
            
            // Delete associated media files
            $action->clearMediaCollection('action_evidence');
            
            ProblemAction::destroy($actionId);
            $this->loadProblems();
            
            \Log::info('ProblemAnalysisManager: Action deleted (with files)', ['id' => $actionId]);
            $this->dispatch('notify', message: '✅ Tindakan berhasil dihapus');
        } catch (\Exception $e) {
            \Log::error('ProblemAnalysisManager: Error deleting action', [
                'id' => $actionId,
                'error' => $e->getMessage(),
            ]);
            $this->dispatch('notify-error', message: '❌ Error: ' . $e->getMessage());
        }
    }

    // ======== FILE UPLOAD METHODS ========
    public function handleFileUpload($files)
    {
        try {
            if (empty($files)) {
                return;
            }

            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $maxFileSize = 5120; // KB (5MB)

            foreach ($files as $file) {
                // Validate file type
                $mimeType = $file->getMimeType();
                if (!in_array($mimeType, $allowedMimes)) {
                    \Log::warning('ProblemAnalysisManager: Invalid file type', [
                        'fileName' => $file->getClientOriginalName(),
                        'mimeType' => $mimeType,
                    ]);
                    $this->dispatch('notify-error', message: '❌ Tipe file tidak didukung: ' . $file->getClientOriginalName());
                    continue;
                }

                // Validate file size
                $fileSizeKB = $file->getSize() / 1024;
                if ($fileSizeKB > $maxFileSize) {
                    \Log::warning('ProblemAnalysisManager: File too large', [
                        'fileName' => $file->getClientOriginalName(),
                        'size' => $fileSizeKB . 'KB',
                    ]);
                    $this->dispatch('notify-error', message: '❌ Ukuran file terlalu besar: ' . $file->getClientOriginalName());
                    continue;
                }

                // Store in temporary location
                $path = $file->store('temp/action-evidence', 'local');
                
                $this->uploadedFiles[] = [
                    'path' => storage_path('app/' . $path),
                    'name' => $file->getClientOriginalName(),
                    'size' => round($fileSizeKB, 2),
                    'type' => $mimeType,
                    'storagePath' => $path,
                ];

                \Log::info('ProblemAnalysisManager: File queued for upload', [
                    'fileName' => $file->getClientOriginalName(),
                    'storagePath' => $path,
                ]);
            }

            $this->dispatch('notify', message: '✅ ' . count($this->uploadedFiles) . ' file(s) siap untuk disimpan');
        } catch (\Exception $e) {
            \Log::error('ProblemAnalysisManager: Error handling file upload', [
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
                
                // Delete from temporary storage
                if (file_exists($file['path'])) {
                    unlink($file['path']);
                }
                
                unset($this->uploadedFiles[$index]);
                $this->uploadedFiles = array_values($this->uploadedFiles); // Reindex array
                
                \Log::info('ProblemAnalysisManager: Uploaded file removed', [
                    'fileName' => $file['name'],
                ]);
                
                $this->dispatch('notify', message: '✅ File dihapus dari antrian upload');
            }
        } catch (\Exception $e) {
            \Log::error('ProblemAnalysisManager: Error removing file', [
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
                \Log::info('ProblemAnalysisManager: File deleted from action', [
                    'actionId' => $actionId,
                    'mediaId' => $mediaId,
                ]);
                $this->dispatch('notify', message: '✅ File dihapus dari tindakan');
                $this->loadProblems();
            }
        } catch (\Exception $e) {
            \Log::error('ProblemAnalysisManager: Error deleting file', [
                'error' => $e->getMessage(),
            ]);
            $this->dispatch('notify-error', message: '❌ Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $problem = null;
        if ($this->editingProblemId) {
            $problem = collect($this->problems)->firstWhere('id', $this->editingProblemId);
        }

        return view('livewire.problem-analysis-manager', [
            'problems' => $this->problems,
            'problem' => $problem,
            'categories' => $this->contributor_categories,
            'components' => $this->contributor_components,
            'subComponents' => $this->contributor_sub_components,
            'showModal' => $this->showModal,
            'activeTab' => $this->activeTab,
            'editingItemType' => $this->editingItemType,
            'editingProblemId' => $this->editingProblemId,
            'whyFormData' => $this->whyFormData,
            'contributorFormData' => $this->contributorFormData,
            'recommendationFormData' => $this->recommendationFormData,
            'actionFormData' => $this->actionFormData,
            'uploadedFiles' => $this->uploadedFiles,
        ]);
    }
}
