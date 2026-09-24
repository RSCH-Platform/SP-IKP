<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskAssessment extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'laporan_insiden_id',
        'severity_score',
        'severity_level',
        'probability_score',
        'probability_level',
        'risk_score',
        'risk_level',
        'risk_band',
        'required_action',
        'assessed_by',
        'assessed_at',
    ];

    protected $casts = [
        'assessed_at' => 'datetime',
    ];

    public function laporanInsiden()
    {
        return $this->belongsTo(LaporanInsiden::class);
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
