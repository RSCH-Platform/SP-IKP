<?php

namespace App\Livewire\Components;

use App\Models\IncidentProblem;
use App\Models\TimelineCategory;
use App\Models\TimelineEntry;
use App\Livewire\Components\Traits\HandlesActions;
use App\Livewire\Components\Traits\HandlesActionFileUploads;
use App\Livewire\Components\Traits\HandlesContributors;
use App\Livewire\Components\Traits\HandlesRecommendations;
use App\Livewire\Components\Traits\HandlesWhys;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProblemAnalysisManager extends Component
{
    use WithFileUploads;
    use HandlesWhys;
    use HandlesContributors;
    use HandlesRecommendations;
    use HandlesActions;
    use HandlesActionFileUploads;

    public $recordId;
    public $problems = [];
    public $expandedProblemId = null;
    public $isReadOnly = false;

    // Modal and form state
    public $activeTab = 'whys'; // whys, contributors, recommendations, actions
    public $editingProblemId = null;
    public $showModal = false; // Keep for backward compatibility, but not used
    public $modalMode = 'view'; // view, add, edit

    // For editing
    public $editingItemId = null;
    public $editingItemType = null;

    // Docker safety: prevent memory bloat during save
    // Note: contributor categories are declared in the HandlesContributors trait

    /**
     * Toggle problem accordion expansion
     */
    public function toggleProblem($problemId)
    {
        $this->expandedProblemId = $this->expandedProblemId === $problemId ? null : $problemId;
    }

    public function mount($recordId = null)
    {
        $this->recordId = $recordId ?? request()->route('record');
        $this->syncReadOnlyState();
        $this->loadProblems();
        $this->loadCategories();
    }

    /**
     * Hydrate lifecycle hook - OPTIMIZED
     * Only loads when data is missing, not on every render
     * CRITICAL: Skip loading during Filament save to prevent memory bloat
     */
    public function hydrate()
    {
        // Sync recordId from route
        if (!$this->recordId && ($recordId = request()->route('record'))) {
            $this->recordId = $recordId;
        }

        // Docker safety: Clear data during save to prevent memory spike
        if ($this->isSaveAction()) {
            // Aggressively clear large data structures during save
            $this->problems = [];
            $this->contributor_categories = [];
            $this->expandedProblemId = null;
            return;
        }

        // Check if observer triggered problem refresh via cache flag
        if ($this->recordId) {
            $cacheKey = "problem-refresh-needed-{$this->recordId}";
            if (Cache::has($cacheKey)) {
                // Observer created/updated/deleted a problem
                // Force reload from database
                $this->loadProblems();
                Cache::forget($cacheKey);
                return;
            }
        }

        // Only load if data empty (prevent excessive queries)
        if ($this->recordId && empty($this->problems)) {
            $this->loadProblems();
        }

        if (empty($this->contributor_categories)) {
            $this->loadCategories();
        }
        if ($this->recordId) {
            $this->syncReadOnlyState();
        }
    }

    private function syncReadOnlyState(): void
    {
        if (!$this->recordId) {
            $this->isReadOnly = false;
            return;
        }

        $status = \App\Models\LaporanInsiden::query()
            ->whereKey($this->recordId)
            ->value('status');

        $this->isReadOnly = $status === \App\Models\LaporanInsiden::STATUS_SELESAI;
    }

    private function ensureEditable(): bool
    {
        $this->syncReadOnlyState();

        if ($this->isReadOnly) {
            $this->dispatch('notify-error', message: 'Laporan sudah selesai. Analisis masalah hanya dalam mode lihat.');
            return false;
        }

        return true;
    }

    /**
     * Detect if request is a Filament save action
     * Works reliably in Docker by checking multiple indicators
     */
    private function isSaveAction(): bool
    {
        $method = request()->input('method');
        $actionName = request()->input('payload.actionName', '');
        $updates = request()->input('payload.updates', []);

        // Filament save calls typically have method=save or specific action
        if ($method === 'save' || str_contains($actionName, 'save')) {
            return true;
        }

        // Check if updating form fields without component action
        if (is_array($updates) && count($updates) > 0 && empty($actionName)) {
            return true; // Form field update - not component action
        }

        return false;
    }

    #[\Livewire\Attributes\On('refresh-problems')]
    public function refreshProblems()
    {
        $this->loadProblems();
    }

    public function syncTimelineEntryProblems(): void
    {
        if (! $this->ensureEditable()) {
            return;
        }

        try {
            $syncedCount = $this->initializeProblemsFromTimeline();

            $this->loadProblems();
            $this->dispatch('refresh-problems');

            if ($syncedCount > 0) {
                $this->dispatch('notify', message: "Sync selesai: {$syncedCount} timeline entry berhasil disinkronkan ke problem.");
                return;
            }

            $this->dispatch('notify', message: 'Tidak ada timeline entry CMP/SDP baru yang perlu disinkronkan.');
        } catch (\Exception $e) {
            Log::error('ProblemAnalysisManager: Error syncing timeline entries to problems', [
                'error' => $e->getMessage(),
                'recordId' => $this->recordId,
            ]);

            $this->dispatch('notify-error', message: 'Gagal sinkron timeline entry ke problem: ' . $e->getMessage());
        }
    }

    public function loadProblems()
    {
        if (!$this->recordId) {
            $this->problems = [];
            return;
        }

        try {
            $incident = \App\Models\LaporanInsiden::with([
                'problems.whys',
                'problems.contributors.category',
                'problems.contributors.component',
                'problems.contributors.subComponent',
                'problems.recommendations',
                'problems.actions.media'
            ])->find($this->recordId);

            if (!$incident || $incident->problems->isEmpty()) {
                $this->problems = [];
                return;
            }

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
                    'contributors' => $problem->contributors->map(function ($c) {
                        $categoryRelation = $c->getRelation('category');
                        $componentRelation = $c->getRelation('component');

                        return [
                            'id' => $c->id,
                            'category' => $categoryRelation?->name ?? $c->category,
                            'component' => $componentRelation?->name ?? $c->component,
                            'sub_component' => $c->subComponent?->name ?? $c->sub_component,
                            'description' => $c->description,
                        ];
                    })->toArray(),
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

            if (empty($this->expandedProblemId) && count($this->problems) > 0) {
                $this->expandedProblemId = $this->problems[0]['id'];
            }

            if (!empty($this->expandedProblemId) && !collect($this->problems)->contains('id', $this->expandedProblemId)) {
                $this->expandedProblemId = $this->problems[0]['id'] ?? null;
            }
        } catch (\Exception $e) {
            $this->problems = [];
        }
    }

    /**
     * Get category name dari ProblemContributor object
     */
    public function getContributorCategoryName($contributor)
    {
        if (is_array($contributor) && isset($contributor['object'])) {
            $obj = $contributor['object'];
            return $obj->category ? $obj->category->name : '(Belum diisi)';
        }
        return '(Belum diisi)';
    }

    /**
     * Get component name dari ProblemContributor object
     */
    public function getContributorComponentName($contributor)
    {
        if (is_array($contributor) && isset($contributor['object'])) {
            $obj = $contributor['object'];
            return $obj->component ? $obj->component->name : '(Belum diisi)';
        }
        return '(Belum diisi)';
    }

    /**
     * Get sub component name dari ProblemContributor object
     */
    public function getContributorSubComponentName($contributor)
    {
        if (is_array($contributor) && isset($contributor['object'])) {
            $obj = $contributor['object'];
            return $obj->subComponent ? $obj->subComponent->name : '(Belum diisi)';
        }
        return '(Belum diisi)';
    }

    public function initializeProblemsFromTimeline(): int
    {
        if (!$this->recordId) {
            return 0;
        }

        if (! $this->ensureEditable()) {
            return 0;
        }

        // Get CMP and SDP timeline categories
        $categoryIds = TimelineCategory::whereIn('code', ['cmp', 'sdp'])
            ->pluck('id')
            ->toArray();

        if (empty($categoryIds)) {
            return 0;
        }

        // Get timeline entries for this specific laporan (through timeline_events)
        $timelineEntries = TimelineEntry::whereHas('event', function ($query) {
            $query->where('laporan_insiden_id', $this->recordId);
        })
            ->whereIn('category_id', $categoryIds)
            ->whereDoesntHave('incidentProblem')
            ->with('category')
            ->orderBy('id')
            ->get();

        if ($timelineEntries->isEmpty()) {
            return 0;
        }

        // Create problems from timeline entries
        try {
            $incident = \App\Models\LaporanInsiden::find($this->recordId);
            if (!$incident) {
                return 0;
            }

            $syncedCount = 0;

            foreach ($timelineEntries as $entry) {
                IncidentProblem::updateOrCreate([
                    'timeline_entry_id' => $entry->id,
                ], [
                    'incident_id' => $this->recordId,
                    'problem_type' => strtoupper($entry->category?->code ?? 'UNKNOWN'),
                    'problem_description' => $entry->description ?? '',
                ]);

                $syncedCount++;
            }

            // Reload problems after creating them
            $this->loadProblems();

            return $syncedCount;
        } catch (\Exception $e) {
            // Silently fail - this is optional initialization
            Log::warning('ProblemAnalysisManager: Error initializing problems from timeline', [
                'error' => $e->getMessage(),
                'recordId' => $this->recordId,
            ]);

            return 0;
        }
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

    private function resetForms()
    {
        $this->whyFormData = [];
        $this->contributorFormData = [];
        $this->recommendationFormData = [];
        $this->actionFormData = [];
        $this->temporaryUploadedFiles = [];
        $this->uploadedFiles = [];
        $this->existingActionMedia = [];
        $this->editingItemId = null;
        $this->editingItemType = null;
    }

    public function resetForm()
    {
        $this->resetForms();
    }

    public function render()
    {
        $problem = null;
        if ($this->editingProblemId) {
            $problem = collect($this->problems)->firstWhere('id', $this->editingProblemId);
        }

        $record = null;
        if ($this->recordId) {
            $record = \App\Models\LaporanInsiden::with([
                'user',
                'creator',
                'unitKerjas',
                'reporter',
                'verifier',
                'rejecter',
                'investigationData',
                'investigationStarter',
                'investigationCompleter',
                'timelineEvents',
                'timelineEntries',
                'problems.whys',
                'problems.contributors.category',
                'problems.contributors.component',
                'problems.contributors.subComponent',
                'problems.recommendations',
                'problems.actions.media',
            ])->find($this->recordId);
        }

        return view('livewire.problem-analysis-manager', [
            'problems' => $this->problems,
            'record' => $record,
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
            'isReadOnly' => $this->isReadOnly,
        ]);
    }
}
