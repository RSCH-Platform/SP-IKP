<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProblemWhy extends Model
{
    use HasFactory;

    protected $table = 'problem_whys';

    protected $fillable = [
        'problem_id',
        'why_level',
        'problem_statement',
        'immediate_cause',
        'root_cause',
    ];

    public function problem(): BelongsTo
    {
        return $this->belongsTo(IncidentProblem::class, 'problem_id');
    }
}
