<?php

namespace App\Livewire\Components;

use App\Models\LaporanInsiden;
use App\Models\TimelineCategory;
use App\Models\TimelineEvent;
use App\Models\TimelineEntry;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class TimelineGridManager extends Component
{
    public $recordId;
    public $timelineEvents = [];
    public $categories = [];

    public $showModal = false;
    public $modalMode = 'edit'; // 'edit', 'add-event', or 'move'

    // Modal fields
    public $editingEventId = null;
    public $editingCategoryId = null;
    public $editingDescription = '';
    public $editingEventDateTime = '';

    // Move category fields
    public $moveSourceCategoryId = null;
    public $moveTargetCategoryId = null;

    public function mount($recordId = null)
    {
        $this->recordId = $recordId ?? request()->route('record');
        Log::info('TimelineGridManager MOUNT', [
            'recordId' => $this->recordId,
            'timestamp' => now(),
        ]);
        $this->loadTimelineData();
        $this->loadCategories();
    }

    /**
     * Hydrate lifecycle hook
     * Runs every time component is rendered/updated
     * Ensures data stays fresh when step visibility changes or on re-render
     * 
     * IMPORTANT: This ALWAYS reloads data to fix issue where data disappears
     * when changing wizard steps. This is more aggressive than checking if empty.
     */
    public function hydrate()
    {
        // Sync recordId from route in case URL changed
        if (!$this->recordId && ($recordId = request()->route('record'))) {
            $this->recordId = $recordId;
        }

        Log::debug('TimelineGridManager HYDRATE', [
            'recordId' => $this->recordId,
            'eventCount' => count($this->timelineEvents),
            'categoryCount' => count($this->categories),
            'timestamp' => now(),
        ]);

        // ALWAYS reload timeline data when rendering
        // Don't check if empty - just always reload to ensure fresh state
        // This fixes data disappearing when wizard steps change
        if ($this->recordId) {
            $this->loadTimelineData();
        }

        // Ensure categories are always available
        if (empty($this->categories)) {
            $this->loadCategories();
        }
    }

    public function loadTimelineData()
    {
        if (!$this->recordId) {
            Log::warning('TimelineGridManager.loadTimelineData: No recordId provided');
            return;
        }

        try {
            Log::debug('TimelineGridManager.loadTimelineData: START', ['recordId' => $this->recordId]);

            // Load record with all related timeline data in one query
            $record = LaporanInsiden::with([
                'timelineEvents' => function ($query) {
                    $query->with(['entries.category'])
                        ->orderBy('event_datetime', 'asc');
                }
            ])->find($this->recordId);

            if ($record && $record->timelineEvents) {
                Log::debug('TimelineGridManager.loadTimelineData: Record found', [
                    'eventCount' => $record->timelineEvents->count(),
                ]);

                $this->timelineEvents = $record->timelineEvents
                    ->map(function ($event) {
                        return [
                            'id' => $event->id,
                            'event_datetime' => $event->event_datetime?->format('Y-m-d H:i:s'),
                            'entries' => $event->entries->map(function ($entry) {
                                return [
                                    'id' => $entry->id,
                                    'category_id' => $entry->category_id,
                                    'category_name' => $entry->category?->name,
                                    'description' => $entry->description,
                                ];
                            })->toArray(),
                        ];
                    })
                    ->toArray();

                Log::info('TimelineGridManager.loadTimelineData: SUCCESS', [
                    'eventCount' => count($this->timelineEvents),
                    'timestamp' => now(),
                ]);
            } else {
                // No events found, ensure empty array
                Log::info('TimelineGridManager.loadTimelineData: No events found for recordId', [
                    'recordId' => $this->recordId,
                    'timestamp' => now(),
                ]);
                $this->timelineEvents = [];
            }
        } catch (\Exception $e) {
            Log::error('TimelineGridManager: Error loading timeline data', [
                'recordId' => $this->recordId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->timelineEvents = [];
        }
    }

    public function loadCategories()
    {
        try {
            $this->categories = TimelineCategory::orderBy('sort_order')
                ->get()
                ->map(fn($cat) => [
                    'id' => $cat->id,
                    'code' => $cat->code,
                    'name' => $cat->name,
                    'sort_order' => $cat->sort_order,
                ])
                ->toArray();

            Log::debug('TimelineGridManager.loadCategories: SUCCESS', [
                'categoryCount' => count($this->categories),
            ]);
        } catch (\Exception $e) {
            Log::error('TimelineGridManager.loadCategories: Error', [
                'error' => $e->getMessage(),
            ]);
            $this->categories = [];
        }
    }

    /**
     * Open modal to add new event
     * 
     * Note: DateTime Format Handling
     * - editingEventDateTime is stored in datetime-local format: YYYY-MM-DDTHH:mm (with T separator)
     * - This format is required by HTML datetime-local input for proper display and binding
     * - When saving, addTimelineEvent() converts T to space for MySQL: YYYY-MM-DD HH:mm
     */
    public function openAddEventModal($date = null)
    {
        $this->modalMode = 'add-event';
        // Format for datetime-local input: YYYY-MM-DDTHH:mm
        if ($date) {
            $dateTime = \Carbon\Carbon::parse($date);
            $this->editingEventDateTime = $dateTime->format('Y-m-d\TH:i');
        } else {
            $this->editingEventDateTime = now()->format('Y-m-d\TH:i');
        }
        $this->showModal = true;
    }

    /**
     * Create new timeline event
     */
    public function addTimelineEvent()
    {
        // Transform datetime format from datetime-local (2026-04-14T08:43) to Y-m-d H:i format
        $dateTime = str_replace('T', ' ', $this->editingEventDateTime);

        $this->validate([
            'editingEventDateTime' => 'required',
        ], [
            'editingEventDateTime.required' => 'Tanggal dan waktu harus diisi',
        ]);

        try {
            // Create timeline event
            $event = TimelineEvent::create([
                'laporan_insiden_id' => $this->recordId,
                'event_datetime' => $dateTime,
                'created_by' => auth()->id(),
            ]);

            // Create entries for all categories (empty)
            foreach ($this->categories as $category) {
                TimelineEntry::create([
                    'timeline_event_id' => $event->id,
                    'category_id' => $category['id'],
                    'description' => '',
                    'created_by' => auth()->id(),
                ]);
            }

            $this->showModal = false;
            $this->resetModal();
            $this->loadTimelineData();

            $this->dispatch('notify', message: 'Event timeline berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('TimelineGridManager: Error adding event', [
                'error' => $e->getMessage(),
                'dateTime' => $dateTime,
            ]);
            $this->dispatch('notify-error', message: 'Gagal menambah event: ' . $e->getMessage());
        }
    }

    /**
     * Open modal to edit entry
     */
    public function openEditModal($eventId, $categoryId)
    {
        $event = $this->timelineEvents[array_search($eventId, array_column($this->timelineEvents, 'id'))] ?? null;

        if (!$event) {
            return;
        }

        $entry = collect($event['entries'])->firstWhere('category_id', $categoryId);

        $this->editingEventId = $eventId;
        $this->editingCategoryId = $categoryId;
        $this->editingDescription = $entry['description'] ?? '';
        $this->editingEventDateTime = $event['event_datetime'] ?? '';
        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function openMoveModal($eventId, $sourceCategoryId)
    {
        $event = $this->timelineEvents[array_search($eventId, array_column($this->timelineEvents, 'id'))] ?? null;

        if (!$event) {
            return;
        }

        $this->editingEventId = $eventId;
        $this->moveSourceCategoryId = $sourceCategoryId;
        $this->moveTargetCategoryId = collect($this->categories)
            ->firstWhere('id', '!=', $sourceCategoryId)['id'] ?? null;

        $this->modalMode = 'move';
        $this->showModal = true;
    }

    /**
     * Save edited entry
     */
    public function saveEntry()
    {
        try {
            $entry = TimelineEntry::where('timeline_event_id', $this->editingEventId)
                ->where('category_id', $this->editingCategoryId)
                ->firstOrFail();

            $entry->update([
                'description' => $this->editingDescription,
                'created_by' => auth()->id(),
            ]);

            $this->showModal = false;
            $this->resetModal();
            $this->loadTimelineData();

            $this->dispatch('notify', message: 'Entry berhasil disimpan');
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: 'Gagal menyimpan entry: ' . $e->getMessage());
        }
    }

    /**
     * Delete timeline entry
     */
    public function deleteEntry($eventId, $categoryId)
    {
        try {
            $event = TimelineEvent::findOrFail($eventId);
            $laporanInsidenId = $event->laporan_insiden_id;

            TimelineEntry::where('timeline_event_id', $eventId)
                ->where('category_id', $categoryId)
                ->delete();

            // Clean up orphaned problems (problems with no children)
            \App\Models\IncidentProblem::where('incident_id', $laporanInsidenId)
                ->whereDoesntHave('whys')
                ->whereDoesntHave('contributors')
                ->whereDoesntHave('recommendations')
                ->whereDoesntHave('actions')
                ->delete();

            $this->loadTimelineData();

            // Emit event to refresh problem analysis in ProblemAnalysisManager
            $this->dispatch('refresh-problems');

            $this->dispatch('notify', message: 'Entry berhasil dihapus');
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: 'Gagal menghapus entry: ' . $e->getMessage());
        }
    }

    public function moveEntry()
    {
        try {
            if (!$this->moveTargetCategoryId || $this->moveTargetCategoryId === $this->moveSourceCategoryId) {
                $this->dispatch('notify-error', message: 'Pilih kategori tujuan yang berbeda');
                return;
            }

            $sourceEntry = TimelineEntry::where('timeline_event_id', $this->editingEventId)
                ->where('category_id', $this->moveSourceCategoryId)
                ->first();

            if (!$sourceEntry) {
                $this->dispatch('notify-error', message: 'Entry sumber tidak ditemukan');
                return;
            }

            $targetEntry = TimelineEntry::where('timeline_event_id', $this->editingEventId)
                ->where('category_id', $this->moveTargetCategoryId)
                ->first();

            if ($targetEntry) {
                if ($sourceEntry->description) {
                    if ($targetEntry->description) {
                        $targetEntry->description = trim($targetEntry->description . "\n\n[Dipindahkan dari kategori lain]\n" . $sourceEntry->description);
                    } else {
                        $targetEntry->description = $sourceEntry->description;
                    }
                    $targetEntry->created_by = auth()->id();
                    $targetEntry->save();
                }

                $sourceEntry->delete();
            } else {
                $sourceEntry->category_id = $this->moveTargetCategoryId;
                $sourceEntry->save();
            }

            $this->showModal = false;
            $this->resetModal();
            $this->loadTimelineData();
            $this->dispatch('notify', message: 'Entry berhasil dipindahkan');
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: 'Gagal memindahkan entry: ' . $e->getMessage());
        }
    }

    /**
     * Delete entire timeline event
     */
    public function deleteEvent($eventId)
    {
        try {
            $event = TimelineEvent::findOrFail($eventId);
            $laporanInsidenId = $event->laporan_insiden_id;

            // Delete the event (cascade will delete TimelineEntry)
            $event->delete();

            // Clean up orphaned problems (problems with no children)
            \App\Models\IncidentProblem::where('incident_id', $laporanInsidenId)
                ->whereDoesntHave('whys')
                ->whereDoesntHave('contributors')
                ->whereDoesntHave('recommendations')
                ->whereDoesntHave('actions')
                ->delete();

            $this->loadTimelineData();

            // Emit event to refresh problem analysis in ProblemAnalysisManager
            $this->dispatch('refresh-problems');

            $this->dispatch('notify', message: 'Event berhasil dihapus');
        } catch (\Exception $e) {
            $this->dispatch('notify-error', message: 'Gagal menghapus event: ' . $e->getMessage());
        }
    }

    /**
     * Close modal and reset fields
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetModal();
    }

    private function resetModal()
    {
        $this->editingEventId = null;
        $this->editingCategoryId = null;
        $this->editingDescription = '';
        $this->editingEventDateTime = '';
        $this->moveSourceCategoryId = null;
        $this->moveTargetCategoryId = null;
    }

    public function eventsByDate()
    {
        return collect($this->timelineEvents)->groupBy(function ($event) {
            return substr($event['event_datetime'] ?? '2000-01-01', 0, 10);
        })->map(function ($events) {
            return $events->sortBy('event_datetime');
        })->sortKeys();
    }

    public function render()
    {
        return view('livewire.timeline-grid-manager', [
            'eventsByDate' => $this->eventsByDate(),
            'categories' => $this->categories,
        ]);
    }
}
