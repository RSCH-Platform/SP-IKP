<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimelineCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'sort_order',
    ];

    /**
     * Entries belonging to this category.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(TimelineEntry::class, 'category_id');
    }
}
