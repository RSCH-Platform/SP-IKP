<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class UnitKerja extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'unit_kerja';

    protected $fillable = [
        'unit_name',
        'description',
        'slug',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->slug = Str::slug($model->unit_name);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_unit_kerja', 'unit_kerja_id', 'user_id')
            ->withTimestamps();
    }

    public function laporanInsiden(): HasMany
    {
        return $this->hasMany(LaporanInsiden::class, 'unit_kerja_id');
    }
}
