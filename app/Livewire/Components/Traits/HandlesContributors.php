<?php

namespace App\Livewire\Components\Traits;

use Illuminate\Support\Facades\Log;

use App\Models\ProblemContributor;
use App\Models\ProblemContributorCategory;
use App\Models\ProblemContributorComponent;
use App\Models\ProblemContributorDescription;
use App\Models\ProblemContributorSubComponent;

trait HandlesContributors
{
    public $contributor_categories = [];
    public $contributor_components = [];
    public $contributor_sub_components = [];
    public $contributorFormData = [];

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
                $this->contributorFormData['description'] = implode("\n", array_map(fn($desc) => "• {$desc}", $descriptions));
            }
        }
    }

    public function updatedContributorFormDataCategoryId()
    {
        $this->onCategoryChange($this->contributorFormData['category_id']);
    }

    public function updatedContributorFormDataComponentId()
    {
        $this->onComponentChange($this->contributorFormData['component_id']);
    }

    public function updatedContributorFormDataSubComponentId()
    {
        $this->onSubComponentChange($this->contributorFormData['sub_component_id']);
    }

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
            'description' => '',
        ];
        $this->contributor_components = [];
        $this->contributor_sub_components = [];
    }

    public function saveContributor()
    {
        try {
            Log::info('ProblemAnalysisManager: saveContributor called', [
                'editingProblemId' => $this->editingProblemId,
                'editingItemId' => $this->editingItemId,
                'contributorFormData' => $this->contributorFormData,
            ]);

            if (empty($this->editingProblemId)) {
                Log::error('ProblemAnalysisManager: editingProblemId is empty!');
                $this->dispatch('notify-error', message: '❌ Problem ID tidak ditemukan');
                return;
            }

            $data = [
                'category_id' => $this->contributorFormData['category_id'] ?? null,
                'component_id' => $this->contributorFormData['component_id'] ?? null,
                'sub_component_id' => $this->contributorFormData['sub_component_id'] ?? null,
                'description' => $this->contributorFormData['description'] ?? null,
            ];

            if ($this->editingItemId) {
                $contributor = ProblemContributor::findOrFail($this->editingItemId);
                $contributor->update($data);
                $message = '✅ Faktor berhasil diperbarui';
            } else {
                $data['problem_id'] = $this->editingProblemId;
                ProblemContributor::create($data);
                $message = '✅ Faktor berhasil ditambahkan';
            }

            $this->loadProblems();
            $this->resetForms();
            $this->dispatch('notify', message: $message);
        } catch (\Exception $e) {
            Log::error('ProblemAnalysisManager: Error saving contributor', [
                'error' => $e->getMessage(),
                'data' => $this->contributorFormData,
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

            if ($contributor->category_id) {
                $this->contributor_components = ProblemContributorComponent::where('category_id', $contributor->category_id)
                    ->orderBy('name')
                    ->get()
                    ->toArray();
            }

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
}
