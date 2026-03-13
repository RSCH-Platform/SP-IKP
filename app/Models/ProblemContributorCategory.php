<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProblemContributorCategory extends Model
{
    use HasFactory;

    protected $table = 'problem_contributor_categories';

    protected $fillable = [
        'name',
        'code',
    ];

    public function components(): HasMany
    {
        return $this->hasMany(ProblemContributorComponent::class, 'category_id');
    }
}
