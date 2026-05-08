<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;

class IncidentProblem extends Model
{
    use HasFactory;

    protected $table = 'incident_problems';

    protected $fillable = [
        'incident_id',
        'timeline_entry_id',
        'problem_type',
        'problem_description',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::saving(function (IncidentProblem $problem) {
            if (! $problem->timeline_entry_id) {
                return;
            }

            $timelineEntry = TimelineEntry::with(['event', 'category'])->find($problem->timeline_entry_id);

            if (! $timelineEntry) {
                throw ValidationException::withMessages([
                    'timeline_entry_id' => 'Timeline entry yang dipilih tidak ditemukan.',
                ]);
            }

            $categoryCode = strtolower($timelineEntry->category?->code ?? '');
            if (! in_array($categoryCode, ['cmp', 'sdp'], true)) {
                throw ValidationException::withMessages([
                    'timeline_entry_id' => 'Timeline entry hanya boleh terkait dengan category code CMP atau SDP.',
                ]);
            }

            if ($problem->incident_id && $timelineEntry->event?->laporan_insiden_id !== $problem->incident_id) {
                throw ValidationException::withMessages([
                    'timeline_entry_id' => 'Timeline entry harus berasal dari laporan insiden yang sama.',
                ]);
            }
        });
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(LaporanInsiden::class, 'incident_id');
    }

    public function timelineEntry(): BelongsTo
    {
        return $this->belongsTo(TimelineEntry::class, 'timeline_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function whys(): HasMany
    {
        return $this->hasMany(ProblemWhy::class, 'problem_id');
    }

    public function contributors(): HasMany
    {
        return $this->hasMany(ProblemContributor::class, 'problem_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(ProblemRecommendation::class, 'problem_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ProblemAction::class, 'problem_id');
    }
}
