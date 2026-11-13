<?php

use App\Models\IncidentProblem;
use App\Models\ProblemWhy;
use App\Models\LaporanInsiden;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('auto-increments why_level up to 5', function () {
    $laporan = LaporanInsiden::factory()->create();
    $problem = IncidentProblem::create([
        'incident_id' => $laporan->id,
        'problem_type' => 'CMP',
        'problem_description' => 'Test Problem',
    ]);

    for ($i = 1; $i <= 5; $i++) {
        $why = ProblemWhy::create([
            'problem_id' => $problem->id,
            'problem_statement' => "Why statement level $i",
        ]);
        expect($why->why_level)->toBe($i);
    }

    expect(fn () => ProblemWhy::create([
        'problem_id' => $problem->id,
        'problem_statement' => 'Why statement level 6',
    ]))->toThrow(\InvalidArgumentException::class);
});
