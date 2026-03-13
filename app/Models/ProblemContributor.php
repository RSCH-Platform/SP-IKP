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
        'sub_component_id',
    ];

    public function problem(): BelongsTo
    {
        return $this->belongsTo(IncidentProblem::class, 'problem_id');
    }

    public function subComponent(): BelongsTo
    {
        return $this->belongsTo(ProblemContributorSubComponent::class, 'sub_component_id');
    }

    /**
     * Get the full hierarchy path for this contributor.
     */
    public function getFullPathAttribute(): string
    {
        if (!$this->subComponent) {
            return 'N/A';
        }

        $subComponent = $this->subComponent;
        $component = $subComponent->component;
        $category = $component->category;

        return "{$category->name} > {$component->name} > {$subComponent->name}";
    }
}
