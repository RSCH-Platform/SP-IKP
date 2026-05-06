<?php

namespace App\Observers;

use App\Models\TimelineEntry;
use App\Models\TimelineCategory;
use App\Models\IncidentProblem;

class TimelineEntryObserver
{
    protected array $targetCodes = ['sdp', 'cmp'];

    public function created(TimelineEntry $entry)
    {
        $this->syncProblemForEntry($entry);
    }

    public function updated(TimelineEntry $entry)
    {
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
                IncidentProblem::firstOrCreate([
                    'incident_id' => $incidentId,
                    'problem_type' => strtoupper($newCode),
                ], [
                    'problem_description' => $entry->description ?? '',
                ]);
            } elseif (! $newIsTarget && $oldIsTarget) {
                // no longer a problem -> remove existing
                IncidentProblem::where('incident_id', $incidentId)
                    ->where('problem_type', strtoupper($oldCode))
                    ->delete();
            } elseif ($newIsTarget && $oldIsTarget && $newCode !== $oldCode) {
                // rename problem type
                $problem = IncidentProblem::where('incident_id', $incidentId)
                    ->where('problem_type', strtoupper($oldCode))
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

            return;
        }

        // If only description changed (or other non-category fields), sync description
        $this->syncProblemForEntry($entry);
    }

    public function deleted(TimelineEntry $entry)
    {
        $code = strtolower($entry->category?->code ?? TimelineCategory::find($entry->getOriginal('category_id'))?->code ?? '');
        if (! in_array($code, $this->targetCodes, true)) {
            return;
        }

        $incidentId = $entry->event?->laporan_insiden_id;
        if (! $incidentId) {
            return;
        }

        IncidentProblem::where('incident_id', $incidentId)
            ->where('problem_type', strtoupper($code))
            ->delete();
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
            'incident_id' => $incidentId,
            'problem_type' => strtoupper($code),
        ]);

        $problem->problem_description = $entry->description ?? $problem->problem_description;
        if (! $problem->exists) {
            // set created_by if possible
            $problem->created_by = $entry->created_by ?? null;
        }

        $problem->save();
    }
}
