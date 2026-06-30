<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LaporanInsidenTransition extends Model
{
    use HasFactory;

    protected $fillable = [
        'laporan_insiden_id',
        'actor_id',
        'from_status',
        'to_status',
        'action_type',
        'reason',
    ];

    public function laporanInsiden()
    {
        return $this->belongsTo(LaporanInsiden::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
