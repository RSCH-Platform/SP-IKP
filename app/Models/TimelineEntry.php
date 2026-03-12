<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'timeline_event_id',
        'category_id',
        'description',
        'created_by',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(TimelineEvent::class, 'timeline_event_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TimelineCategory::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
