<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimelineEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'laporan_insiden_id',
        'event_datetime',
        'created_by',
    ];

    protected $casts = [
        'event_datetime' => 'datetime',
    ];

    public function laporanInsiden(): BelongsTo
    {
        return $this->belongsTo(LaporanInsiden::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TimelineEntry::class);
    }
}
