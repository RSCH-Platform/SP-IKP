<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Traits\InteractsWithMediaFolders;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class InvestigationData extends Model implements HasMedia
{
    use HasFactory, InteractsWithMediaFolders, InteractsWithMedia;

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

    protected $appends = [
        'file_path',
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('investigation_documents')
            ->useDisk(config('media-library.disk_name', 'public'))
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/gif',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    }

    public function getMediaFolderPath(): string
    {
        $laporan = $this->laporanInsiden;

        if (! $laporan) {
            return '';
        }

        $unitName = $laporan->unitKerja?->unit_name
            ?? $laporan->unit_kerja
            ?? 'unit-kerja-tidak-diketahui';

        $unitSlug = Str::slug($unitName, '-');
        $month = optional($laporan->tanggal_lapor)->format('Y-m')
            ?? optional($laporan->tanggal_insiden)->format('Y-m')
            ?? date('Y-m');

        $reportSegment = $laporan->nomor_laporan
            ? Str::slug($laporan->nomor_laporan, '-')
            : "laporan-{$laporan->id}";

        return trim("{$unitSlug}/Laporan Insiden/{$month}/{$reportSegment}", '/');
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

    public function getFilePathAttribute($value)
    {
        if (! empty($value)) {
            return $value;
        }

        if ($this->relationLoaded('media')) {
            $media = $this->media->firstWhere('collection_name', 'investigation_documents');
            if ($media) {
                return $media->getUrl();
            }
        }

        $media = $this->getFirstMedia('investigation_documents');
        if (! $media) {
            return null;
        }

        return $media->getUrl();
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            // Auto-set created_by if not provided
            if (empty($model->created_by)) {
                $model->created_by = Auth::id();
            }
        });
    }
}
