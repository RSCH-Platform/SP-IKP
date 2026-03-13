<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncidentProblem extends Model
{
    use HasFactory;

    protected $table = 'incident_problems';

    protected $fillable = [
        'incident_id',
        'problem_type',
        'problem_description',
        'created_by',
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(LaporanInsiden::class, 'incident_id');
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
