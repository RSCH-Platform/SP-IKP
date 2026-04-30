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
        'category_id',
        'component_id',
        'sub_component_id',
        'description',
    ];

    protected $appends = [
        'category_name',
        'component_name',
        'sub_component_name',
    ];

    public function problem(): BelongsTo
    {
        return $this->belongsTo(IncidentProblem::class, 'problem_id');
    }

    public function subComponent(): BelongsTo
    {
        return $this->belongsTo(ProblemContributorSubComponent::class, 'sub_component_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProblemContributorCategory::class, 'category_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(ProblemContributorComponent::class, 'component_id');
    }

    /**
     * Get the full hierarchy path for this contributor.
     */
    public function getCategoryNameAttribute(): ?string
    {
        $relation = $this->getRelation('category');
        if ($relation) {
            return $relation->name;
        }

        return $this->getAttribute('category');
    }

    public function getComponentNameAttribute(): ?string
    {
        $relation = $this->getRelation('component');
        if ($relation) {
            return $relation->name;
        }

        return $this->getAttribute('component');
    }

    public function getSubComponentNameAttribute(): ?string
    {
        $relation = $this->getRelation('subComponent');
        if ($relation) {
            return $relation->name;
        }

        return $this->getAttribute('sub_component');
    }

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
