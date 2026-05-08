<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TimelineEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'timeline_event_id',
        'category_id',
        'description',
        'created_by',
    ];

    /**
     * Prevent unique key violations by updating an existing entry when one already exists
     * for the same (timeline_event_id, category_id) pair.
     *
     * This is used because Filament's Repeater will often create new models, even if a
     * matching record already exists.
     */
    public function save(array $options = []): bool
    {
        if (! $this->exists && $this->timeline_event_id && $this->category_id) {
            $existing = self::where('timeline_event_id', $this->timeline_event_id)
                ->where('category_id', $this->category_id)
                ->first();

            if ($existing) {
                $existing->description = $this->description;
                $existing->created_by = $this->created_by ?? $existing->created_by;
                $existing->save();

                // Sync current instance with the persisted record so Filament can continue normally.
                $this->setRawAttributes($existing->getAttributes(), true);
                $this->exists = true;

                return true;
            }
        }

        return parent::save($options);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(TimelineEvent::class, 'timeline_event_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TimelineCategory::class);
    }

    public function incidentProblem(): HasOne
    {
        return $this->hasOne(IncidentProblem::class, 'timeline_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
