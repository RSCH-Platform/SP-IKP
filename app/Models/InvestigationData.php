<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestigationData extends Model
{
    use HasFactory;

    // Investigation category constants
    const KATEGORI_INTERVIEW = 'interview';
    const KATEGORI_REVIEW_DOKUMEN = 'review_dokumen';
    const KATEGORI_OBSERVASI = 'observasi';

    protected $table = 'investigation_data';

    protected $fillable = [
        'laporan_insiden_id',
        'kategori',
        'sumber',
        'hasil',
        'lokasi',
        'file_path',
        'created_by',
    ];

    protected $casts = [
        'investigated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the laporan insiden that this investigation data belongs to
     */
    public function laporanInsiden(): BelongsTo
    {
        return $this->belongsTo(LaporanInsiden::class);
    }

    /**
     * Get the user who investigated (performed the investigation)
     */
    public function investigator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigated_by');
    }

    /**
     * Get the user who created this investigation data record
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get available categories for selection
     */
    public static function getKategoriOptions(): array
    {
        return [
            self::KATEGORI_INTERVIEW => 'Interview',
            self::KATEGORI_REVIEW_DOKUMEN => 'Review Dokumen',
            self::KATEGORI_OBSERVASI => 'Observasi',
        ];
    }

    /**
     * Get category label
     */
    public function getKategoriLabel(): string
    {
        $labels = self::getKategoriOptions();
        return $labels[$this->kategori] ?? $this->kategori;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            // Auto-set created_by if not provided
            if (empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });
    }
}
