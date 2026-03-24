<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProblemAction extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $table = 'problem_actions';

    protected $fillable = [
        'problem_id',
        'action_text',
        'responsible_person',
        'deadline',
        'status',
        'evidence_files',
    ];

    protected $casts = [
        'deadline' => 'date',
        'evidence_files' => 'array',
    ];

    public function problem(): BelongsTo
    {
        return $this->belongsTo(IncidentProblem::class, 'problem_id');
    }


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('action_evidence')
            ->useDisk('public')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/gif',
                'application/pdf',
            ]);
    }
}
