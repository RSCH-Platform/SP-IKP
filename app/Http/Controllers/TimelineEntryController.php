<?php

namespace App\Http\Controllers;

use App\Models\TimelineCategory;
use App\Models\TimelineEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimelineEntryController extends Controller
{
    /**
     * Display a listing of timeline entries.
     */
    public function index(Request $request)
    {
        $query = TimelineEntry::with(['event', 'category', 'creator'])
            ->when($request->filled('date'), function ($q) use ($request) {
                $q->whereHas('event', function ($q2) use ($request) {
                    $q2->whereDate('event_datetime', $request->date);
                });
            })
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->where('category_id', $request->category);
            })
            ->where('created_by', $request->user()->id)
            ->orderBy('created_at', 'desc');

        $entries = $query->get();

        $columnKeys = [];
        foreach ($entries->sortBy(fn($e) => optional($e->event)->event_datetime) as $entry) {
            if (! $entry->event || ! $entry->event->event_datetime) {
                continue;
            }

            $columnKeys[] = $entry->event->event_datetime->format('Y-m-d H:i');
        }
        $columnKeys = array_values(array_unique($columnKeys));

        $timeline = collect($columnKeys)
            ->groupBy(fn($key) => substr($key, 0, 10))
            ->map(function ($items, $date) {
                return [
                    'date' => Carbon::createFromFormat('Y-m-d', $date)->translatedFormat('j F Y'),
                    'entries' => collect($items)
                        ->map(fn($value) => Carbon::createFromFormat('Y-m-d H:i', $value)->format('H.i'))
                        ->all(),
                ];
            })
            ->values()
            ->all();

        $preferredRowOrder = [
            'KEJADIAN',
            'INFORMASI TAMBAHAN',
            'GOOD PRACTICE',
            'MASALAH (CMP)',
            'MASALAH (SDP)',
        ];

        $categories = TimelineCategory::orderBy('sort_order')->get();
        $rowLabels = array_unique(array_merge($preferredRowOrder, $categories->pluck('name')->all()));

        $rows = [];
        foreach ($rowLabels as $label) {
            $rows[$label] = array_fill(0, count($columnKeys), '');
        }

        foreach ($entries as $entry) {
            if (! $entry->event || ! $entry->event->event_datetime) {
                continue;
            }

            $label = $entry->category?->name ?? 'Lainnya';
            if (! array_key_exists($label, $rows)) {
                $rows[$label] = array_fill(0, count($columnKeys), '');
            }

            $key = $entry->event->event_datetime->format('Y-m-d H:i');
            $index = array_search($key, $columnKeys, true);
            if ($index !== false) {
                $rows[$label][$index] = $entry->description;
            }
        }

        return view('timeline-entries', [
            'timeline' => $timeline,
            'rows' => $rows,
            'categories' => $categories,
            'filters' => [
                'date' => $request->input('date'),
                'category' => $request->input('category'),
            ],
        ]);
    }
}
