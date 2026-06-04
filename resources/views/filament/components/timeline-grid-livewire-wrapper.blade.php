@php
    // Get recordId from multiple sources since it's inside a Filament form
    $recordId = $record?->id
        ?? request()->route('record')
        ?? request()->query('record')
        ?? (request()->route('parameters')[0] ?? null);

    \Illuminate\Support\Facades\Log::debug('timeline-grid-livewire-wrapper', [
        'recordId' => $recordId,
        'record_exists' => $record ? true : false,
        'route_record' => request()->route('record'),
    ]);
@endphp

<div class="space-y-4">
    @if($recordId)
        @livewire('components.timeline-grid-manager', [
            'recordId' => $recordId,
        ], key('timeline-grid-' . $recordId))
    @else
    @endif
</div>