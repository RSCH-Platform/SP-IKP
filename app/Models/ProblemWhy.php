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

    protected static function boot()
    {
        parent::boot();

        // Auto-set why_level if not provided
        static::creating(function ($model) {
            if (empty($model->why_level) && $model->problem_id) {
                $maxLevel = static::where('problem_id', $model->problem_id)->max('why_level') ?? 0;
                $model->why_level = min($maxLevel + 1, 5); // Max 5 for 5-WHY
            }
        });
    }

    public function problem(): BelongsTo
    {
        return $this->belongsTo(IncidentProblem::class, 'problem_id');
    }
}
