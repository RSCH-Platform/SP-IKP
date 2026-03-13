<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProblemContributor extends Model
{
    use HasFactory;

    protected $table = 'problem_contributors';

    protected $fillable = [
        'problem_id',
        'category',
        'component',
        'sub_component',
        'description',
    ];

    public function problem(): BelongsTo
    {
        return $this->belongsTo(IncidentProblem::class, 'problem_id');
    }
}
