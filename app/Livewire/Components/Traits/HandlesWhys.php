<?php

namespace App\Livewire\Components\Traits;

use App\Models\ProblemWhy;

trait HandlesWhys
{
    public $whyFormData = [];

    public function addWhy($problemId = null)
    {
        if ($problemId) {
            $this->editingProblemId = $problemId;
        }

        if ($this->editingProblemId && ProblemWhy::where('problem_id', $this->editingProblemId)->count() >= 5) {
            $this->dispatch('notify-error', message: 'Maksimal 5 WHY untuk satu masalah.');
            return;
        }

        $this->editingItemId = null;
        $this->editingItemType = 'why';
        $this->whyFormData = [
            'id' => null,
            'problem_statement' => '',
        ];
    }

    public function saveWhy()
    {
        try {
            if ($this->editingItemId) {
                $why = ProblemWhy::findOrFail($this->editingItemId);
                $why->update(['problem_statement' => $this->whyFormData['problem_statement']]);
            } else {
                $count = ProblemWhy::where('problem_id', $this->editingProblemId)->count();
                if ($count >= 5) {
                    $this->dispatch('notify-error', message: 'Maksimal 5 WHY untuk satu masalah.');
                    return;
                }

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
        $this->whyFormData = [
            'id' => $whyId,
            'problem_statement' => $why->problem_statement,
        ];
    }
}
