@livewire('components.timeline-grid-manager', [
    'recordId' => request()->route('record'),
], key('timeline-grid-' . request()->route('record')))
