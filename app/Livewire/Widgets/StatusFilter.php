<?php

namespace App\Livewire\Widgets;

use Livewire\Component;

class StatusFilter extends Component
{
    public array $statusFilter = ['investigasi', 'selesai_investigasi'];

    #[\Livewire\Attributes\Locked]
    public bool $initialized = false;

    public function mount(array $statusFilter = []): void
    {
        if (!empty($statusFilter)) {
            $this->statusFilter = $statusFilter;
        }
        $this->initialized = true;
    }

    public function updated($property, $value): void
    {
        if ($property === 'statusFilter' && $this->initialized) {
            // Sync with parent
            $this->dispatch('status-filter-changed', statuses: $this->statusFilter);
        }
    }

    public function toggleStatus(string $status): void
    {
        if (in_array($status, $this->statusFilter)) {
            $this->statusFilter = array_values(array_diff($this->statusFilter, [$status]));
        } else {
            $this->statusFilter[] = $status;
        }

        // Dispatch event to parent widget
        $this->dispatch('status-filter-changed', statuses: $this->statusFilter);
    }

    public function render()
    {
        return view('livewire.widgets.status-filter');
    }
}
