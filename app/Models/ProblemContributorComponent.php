<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProblemContributorComponent extends Model
{
    use HasFactory;

    protected $table = 'problem_contributor_components';

    protected $fillable = [
        'category_id',
        'name',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProblemContributorCategory::class, 'category_id');
    }

    public function subComponents(): HasMany
    {
        return $this->hasMany(ProblemContributorSubComponent::class, 'component_id');
    }
}
