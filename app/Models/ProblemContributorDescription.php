<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProblemContributorDescription extends Model
{
    use HasFactory;

    protected $table = 'problem_contributor_descriptions';

    protected $fillable = [
        'sub_component_id',
        'description',
    ];

    public function subComponent(): BelongsTo
    {
        return $this->belongsTo(ProblemContributorSubComponent::class, 'sub_component_id');
    }
}
