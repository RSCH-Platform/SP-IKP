<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProblemAction extends Model
{
    use HasFactory;

    protected $table = 'problem_actions';

    protected $fillable = [
        'problem_id',
        'action_text',
        'responsible_person',
        'deadline',
        'status',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function problem(): BelongsTo
    {
        return $this->belongsTo(IncidentProblem::class, 'problem_id');
    }
}
