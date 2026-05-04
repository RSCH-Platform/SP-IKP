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

    // Modal and form state
    public $activeTab = 'whys'; // whys, contributors, recommendations, actions
    public $editingProblemId = null;
    public $showModal = false; // Keep for backward compatibility, but not used
    public $modalMode = 'view'; // view, add, edit

    // For editing
    public $editingItemId = null;
    public $editingItemType = null;

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

        // Skip loading if this is a Filament save action to prevent memory spike
        if ($this->isSaveAction()) {
            return;
        }

        // Only load if data empty (prevent excessive queries)
        if ($this->recordId && empty($this->problems)) {
            $this->loadProblems();
        }

        if (empty($this->categories)) {
            $this->loadCategories();
        }
    }

    /**
     * Detect if request is a Filament save action
     * Prevents loading large data during form saves
     */
    private function isSaveAction(): bool
    {
        return request()->has('__livewire') && 
               (request()->input('method') === 'save' || 
                str_contains(request()->input('payload.actionName', ''), 'save'));
    }

    #[\Livewire\Attributes\On('refresh-problems')]
    public function refreshProblems()
    {
        $this->loadProblems();
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

    public function initializeProblemsFromTimeline()
    {
        if (!$this->recordId) {
            return;
        }

        // Get CMP and SDP timeline categories
        $categoryIds = TimelineCategory::whereIn('code', ['cmp', 'sdp'])
            ->pluck('id')
            ->toArray();

        if (empty($categoryIds)) {
            return;
        }

        // Get timeline entries for this specific laporan (through timeline_events)
        $timelineEntries = TimelineEntry::whereHas('event', function ($query) {
            $query->where('laporan_insiden_id', $this->recordId);
        })
            ->whereIn('category_id', $categoryIds)
            ->with('category')
            ->orderBy('id')
            ->get()
            ->groupBy(fn(TimelineEntry $entry) => strtoupper($entry->category?->code ?? ''))
            ->map(fn($group) => $group->first());

        if (empty($timelineEntries)) {
            return;
        }

        // Create problems from timeline entries
        try {
            $incident = \App\Models\LaporanInsiden::find($this->recordId);
            if (!$incident) {
                return;
            }

            foreach ($timelineEntries as $entry) {
                IncidentProblem::create([
                    'incident_id' => $this->recordId,
                    'problem_type' => strtoupper($entry->category?->code ?? 'UNKNOWN'),
                    'problem_description' => $entry->description ?? '',
                ]);
            }

            // Reload problems after creating them
            $this->loadProblems();
        } catch (\Exception $e) {
            // Silently fail - this is optional initialization
            Log::warning('ProblemAnalysisManager: Error initializing problems from timeline', [
                'error' => $e->getMessage(),
                'recordId' => $this->recordId,
            ]);
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
