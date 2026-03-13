<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProblemRecommendation extends Model
{
    use HasFactory;

    protected $table = 'problem_recommendations';

    protected $fillable = [
        'problem_id',
        'recommendation_text',
        'priority',
    ];

    public function problem(): BelongsTo
    {
        return $this->belongsTo(IncidentProblem::class, 'problem_id');
    }
}
