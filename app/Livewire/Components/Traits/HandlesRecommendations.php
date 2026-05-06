<?php

namespace App\Livewire\Components\Traits;

use App\Models\ProblemRecommendation;

trait HandlesRecommendations
{
    public $recommendationFormData = [];

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
                $recommendation = ProblemRecommendation::findOrFail($this->editingItemId);
                $recommendation->update($data);
                $message = 'Rekomendasi berhasil diperbarui';
            } else {
                $data['problem_id'] = $this->editingProblemId;
                ProblemRecommendation::create($data);
                $message = 'Rekomendasi berhasil ditambahkan';
            }

            $this->loadProblems();
            $this->resetForms();
            $this->dispatch('notify', message: $message);
            $this->dispatch('close-recommendation-modal');
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
}
