<?php

use App\Models\IncidentProblem;
use App\Models\LaporanInsiden;
use App\Models\TimelineCategory;
use App\Models\TimelineEntry;
use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function createIncidentWithTimelineEntry(string $categoryCode): array
{
    $user = User::factory()->create();

    $incident = LaporanInsiden::create([
        'user_id' => $user->id,
        'nama_pelapor' => 'Pelapor Uji',
        'unit_kerja' => 'IGD',
        'tanggal_lapor' => '2026-05-08',
        'nomor_laporan' => 'TEST-' . strtoupper($categoryCode) . '-' . uniqid(),
        'jenis_insiden' => 'KTD (Kejadian Tidak Diharapkan)',
        'tanggal_insiden' => '2026-05-08',
        'waktu_insiden' => '08:00:00',
        'lokasi_insiden' => 'IGD',
        'kategori_insiden' => 'Komunikasi',
    ]);

    $category = TimelineCategory::create([
        'code' => $categoryCode,
        'name' => strtoupper($categoryCode),
        'sort_order' => 1,
    ]);

    $event = TimelineEvent::create([
        'laporan_insiden_id' => $incident->id,
        'event_datetime' => '2026-05-08 08:00:00',
        'created_by' => $user->id,
    ]);

    $entry = TimelineEntry::create([
        'timeline_event_id' => $event->id,
        'category_id' => $category->id,
        'description' => 'Deskripsi uji ' . $categoryCode,
        'created_by' => $user->id,
    ]);

    return [$incident, $category, $event, $entry, $user];
}

test('incident problem can link only to cmp or sdp timeline entries', function () {
    [, , , $cmpEntry] = createIncidentWithTimelineEntry('cmp');

    $problem = IncidentProblem::where('timeline_entry_id', $cmpEntry->id)->firstOrFail();

    expect($problem->timelineEntry?->id)->toBe($cmpEntry->id)
        ->and($cmpEntry->incidentProblem?->id)->toBe($problem->id);
});

test('incident problem allows null timeline entry', function () {
    [$incident] = createIncidentWithTimelineEntry('cmp');

    $problem = IncidentProblem::create([
        'incident_id' => $incident->id,
        'timeline_entry_id' => null,
        'problem_type' => 'CMP',
        'problem_description' => 'Manual problem',
    ]);

    expect($problem->timeline_entry_id)->toBeNull();
});

test('incident problem rejects non cmp or sdp timeline entry', function () {
    [, , , $entry] = createIncidentWithTimelineEntry('informasi');

    expect(fn () => IncidentProblem::create([
        'incident_id' => $entry->event->laporan_insiden_id,
        'timeline_entry_id' => $entry->id,
        'problem_type' => 'INFO',
        'problem_description' => 'Tidak boleh terkait',
    ]))->toThrow(ValidationException::class);
});