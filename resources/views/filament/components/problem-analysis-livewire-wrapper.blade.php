@livewire('components.problem-analysis-manager', [
    'recordId' => request()->route('record'),
], key('problem-analysis-' . request()->route('record')))
