<?php

namespace App\Observers;

use App\Models\TimelineEntry;
use App\Models\TimelineCategory;
use App\Models\IncidentProblem;
use Illuminate\Support\Facades\Cache;

class TimelineEntryObserver
{
    protected array $targetCodes = ['sdp', 'cmp'];

    public function created(TimelineEntry $entry)
    {
        if ($this->shouldDeleteEmptyEntry($entry)) {
            $entry->delete();
            return;
        }

        $this->syncProblemForEntry($entry);
        $this->notifyProblemRefresh($entry);
    }

    public function updated(TimelineEntry $entry)
    {
        if ($this->shouldDeleteEmptyEntry($entry)) {
            $entry->delete();
            return;
        }

        $originalCategoryId = $entry->getOriginal('category_id');
        $newCategoryId = $entry->category_id;

        // If category changed, handle create/delete/rename
        if ($originalCategoryId !== $newCategoryId) {
            $old = TimelineCategory::find($originalCategoryId);
            $oldCode = strtolower($old?->code ?? '');
            $newCode = strtolower($entry->category?->code ?? '');

            $incidentId = $entry->event?->laporan_insiden_id;
            if (! $incidentId) {
                return;
            }

            $oldIsTarget = in_array($oldCode, $this->targetCodes, true);
            $newIsTarget = in_array($newCode, $this->targetCodes, true);

            if ($newIsTarget && ! $oldIsTarget) {
                // became a problem -> create if missing
                IncidentProblem::updateOrCreate([
                    'timeline_entry_id' => $entry->id,
                ], [
                    'incident_id' => $incidentId,
                    'problem_type' => strtoupper($newCode),
                    'problem_description' => $entry->description ?? '',
                ]);
            } elseif (! $newIsTarget && $oldIsTarget) {
                // no longer a problem -> remove existing
                IncidentProblem::where('timeline_entry_id', $entry->id)
                    ->delete();
            } elseif ($newIsTarget && $oldIsTarget && $newCode !== $oldCode) {
                // rename problem type
                $problem = IncidentProblem::where('timeline_entry_id', $entry->id)
                    ->first();

                if ($problem) {
                    $problem->update([
                        'problem_type' => strtoupper($newCode),
                        'problem_description' => $entry->description ?? $problem->problem_description,
                    ]);
                } else {
                    IncidentProblem::create([
                        'incident_id' => $incidentId,
                        'problem_type' => strtoupper($newCode),
                        'problem_description' => $entry->description ?? '',
                    ]);
                }
            }

            $this->notifyProblemRefresh($entry);
            return;
        }

        // If only description changed (or other non-category fields), sync description
        $this->syncProblemForEntry($entry);
        $this->notifyProblemRefresh($entry);
    }

    public function deleted(TimelineEntry $entry)
    {
        IncidentProblem::where('timeline_entry_id', $entry->id)
            ->delete();
        $this->notifyProblemRefresh($entry);
    }

    protected function syncProblemForEntry(TimelineEntry $entry): void
    {
        $code = strtolower($entry->category?->code ?? '');
        if (! in_array($code, $this->targetCodes, true)) {
            return;
        }

        $incidentId = $entry->event?->laporan_insiden_id;
        if (! $incidentId) {
            return;
        }

        $problem = IncidentProblem::firstOrNew([
            'timeline_entry_id' => $entry->id,
        ]);

        $problem->incident_id = $incidentId;
        $problem->problem_type = strtoupper($code);
        $problem->problem_description = $entry->description ?? $problem->problem_description;
        if (! $problem->exists) {
            // set created_by if possible
            $problem->created_by = $entry->created_by ?? null;
        }

        $problem->save();
    }

    protected function shouldDeleteEmptyEntry(TimelineEntry $entry): bool
    {
        return trim((string) $entry->description) === '';
    }

    /**
     * Notify Livewire component to refresh problems
     * Uses cache key to signal component across request boundaries
     * Component checks this flag in hydrate() and reloads data if needed
     */
    protected function notifyProblemRefresh(TimelineEntry $entry): void
    {
        $incidentId = $entry->event?->laporan_insiden_id;
        if (! $incidentId) {
            return;
        }

        // Set a cache flag that component will check in hydrate
        Cache::put("problem-refresh-needed-{$incidentId}", time(), now()->addMinutes(1));
    }
}
