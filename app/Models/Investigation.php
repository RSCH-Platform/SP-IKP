<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Investigation extends Model
{
    use HasFactory;

    protected $fillable = [
        'laporan_insiden_id',
        'grading_risiko',
        'status',
        'investigation_started_by',
        'investigation_started_at',
        'investigation_completed_by',
        'investigation_completed_at',
    ];

    protected $casts = [
        'investigation_started_at' => 'datetime',
        'investigation_completed_at' => 'datetime',
    ];

    public function laporanInsiden()
    {
        return $this->belongsTo(LaporanInsiden::class);
    }

    public function startedBy()
    {
        return $this->belongsTo(User::class, 'investigation_started_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'investigation_completed_by');
    }
}
