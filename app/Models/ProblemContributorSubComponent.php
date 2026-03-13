<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProblemContributorSubComponent extends Model
{
    use HasFactory;

    protected $table = 'problem_contributor_sub_components';

    protected $fillable = [
        'component_id',
        'name',
    ];

    public function component(): BelongsTo
    {
        return $this->belongsTo(ProblemContributorComponent::class, 'component_id');
    }

    public function descriptions(): HasMany
    {
        return $this->hasMany(ProblemContributorDescription::class, 'sub_component_id');
    }
}
