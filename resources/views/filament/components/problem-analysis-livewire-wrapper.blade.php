@php
// Get recordId from multiple sources since it's inside a Filament form
$recordId = $record?->id
?? request()->route('record')
?? request()->query('record')
?? (request()->route('parameters')[0] ?? null);

\Illuminate\Support\Facades\Log::debug('problem-analysis-livewire-wrapper', [
'recordId' => $recordId,
'record_exists' => $record ? true : false,
'route_record' => request()->route('record'),
]);
@endphp

@if($recordId)
@livewire('components.problem-analysis-manager', [
'recordId' => $recordId,
], key('problem-analysis-' . $recordId))
@else
<div class="p-4 bg-yellow-50 border border-yellow-200 rounded text-yellow-800 text-sm">
    ⚠️ DEBUG: recordId tidak ditemukan.
    Record: {{ $record?->id ?? 'null' }} |
    Route: {{ request()->route('record') ?? 'null' }}
</div>
@endif