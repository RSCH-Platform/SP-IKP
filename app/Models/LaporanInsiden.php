<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\TimelineEvent;
use App\Models\TimelineEntry;

class LaporanInsiden extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    const STATUS_DRAFT          = 'draft';
    const STATUS_DILAPORKAN     = 'dilaporkan';
    const STATUS_REVISI         = 'revisi';         // kepala_unit → pelapor
    const STATUS_DIVERIFIKASI   = 'diverifikasi';   // kepala_unit selesai grading & analisis
    const STATUS_REVISI_UNIT    = 'revisi_unit';    // tim_mutu → kepala_unit
    const STATUS_INVESTIGASI    = 'investigasi';    // tim_mutu sedang investigasi sederhana

    protected $fillable = [
        'user_id',
        'unit_kerja_id',
        'nama_pelapor',
        'unit_kerja',
        'nomor_telepon',
        'tanggal_lapor',
        'nomor_laporan',
        'jenis_insiden',
        'tanggal_insiden',
        'waktu_insiden',
        'lokasi_insiden',
        'nama_pasien',
        'nomor_rekam_medis',
        'ruangan',
        'umur',
        'kelompok_umur',
        'jenis_kelamin',
        'penanggung_biaya',
        'tanggal_masuk_rs',
        'pelapor_insiden_pasien',
        'pelapor_insiden_pasien_lainnya',
        'insiden_menyangkut_pasien',
        'insiden_menyangkut_pasien_lainnya',
        'spesialisasi_pasien',
        'spesialisasi_pasien_lainnya',
        'insiden_terjadi_pada',
        'insiden_terjadi_pada_lainnya',
        'dampak_insiden',
        'kategori_insiden',
        'deskripsi_kategori_insiden',
        'tindakan_dilakukan',
        'tindakan_dilakukan_oleh',
        'tindakan_dilakukan_oleh_lainnya',
        'kejadian_pernah_terjadi_sebelumnya',
        'kejadian_pernah_terjadi_sebelumnya_deskripsi',
        'grading_risiko',
        'catatan_tambahan',
        'status',
        // Workflow columns
        'reported_by',
        'reported_at',
        'verified_by',
        'verified_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        // Investigation flow columns
        'investigation_started_by',
        'investigation_started_at',
        'investigation_completed_by',
        'investigation_completed_at',
    ];

    protected $casts = [
        'tanggal_lapor'   => 'date',
        'tanggal_insiden' => 'date',
        'tanggal_masuk_rs' => 'datetime',
        'reported_at'     => 'datetime',
        'verified_at'     => 'datetime',
        'rejected_at'     => 'datetime',
        'investigation_started_at' => 'datetime',
        'investigation_completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function unitKerjas(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function investigationData(): HasMany
    {
        return $this->hasMany(InvestigationData::class);
    }

    public function investigationStarter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigation_started_by');
    }

    public function investigationCompleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigation_completed_by');
    }

    /**
     * Events that belong to this incident report.
     */
    public function timelineEvents(): HasMany
    {
        return $this->hasMany(TimelineEvent::class);
    }

    /**
     * Shortcut to all timeline entries through events.
     */
    public function timelineEntries(): HasManyThrough
    {
        return $this->hasManyThrough(TimelineEntry::class, TimelineEvent::class);
    }

    /**
     * Root cause analysis problems for this incident.
     */
    public function problems(): HasMany
    {
        return $this->hasMany(IncidentProblem::class, 'incident_id');
    }

    // --- Workflow transition methods ---

    /** Pelapor mengirim laporan ke kepala unit */
    public function submitLaporan(): void
    {
        $this->update([
            'status'      => self::STATUS_DILAPORKAN,
            'reported_by' => auth()->id(),
            'reported_at' => now(),
        ]);
    }

    /** Kepala unit memverifikasi (selesai grading & analisis), teruskan ke tim mutu */
    public function verifikasiLaporan(int $userId): void
    {

        if ($this->grading_risiko === null) {
            throw new \Exception('Laporan harus memiliki grading risiko untuk diverifikasi.');
        }

        $this->update([
            'status'      => self::STATUS_DIVERIFIKASI,
            'verified_by' => $userId,
            'verified_at' => now(),
            // Ensure reported_by and reported_at are set if not already (defensive)
            'reported_by' => $this->reported_by ?? auth()->id(),
            'reported_at' => $this->reported_at ?? now(),
        ]);
    }

    /** Kepala unit mengembalikan laporan ke pelapor untuk diperbaiki */
    public function kembalikanKePelapor(int $userId, string $reason): void
    {
        $this->update([
            'status'           => self::STATUS_REVISI,
            'rejected_by'      => $userId,
            'rejected_at'      => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /** Tim mutu memulai investigasi sederhana */
    public function mulaiInvestigasi(int $userId): void
    {
        $this->update([
            'status'                    => self::STATUS_INVESTIGASI,
            'investigation_started_by'  => $userId,
            'investigation_started_at'  => now(),
            // Ensure reported_by and reported_at are set if not already (defensive)
            'reported_by' => $this->reported_by ?? auth()->id(),
            'reported_at' => $this->reported_at ?? now(),
        ]);
    }

    /** Tim mutu mengembalikan laporan ke kepala unit untuk diperbaiki */
    public function kembalikanKeKepalaUnit(int $userId, string $reason): void
    {
        $this->update([
            'status'           => self::STATUS_REVISI_UNIT,
            'rejected_by'      => $userId,
            'rejected_at'      => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /** Tim mutu menyelesaikan investigasi */
    public function selesaikanInvestigasi(int $userId): void
    {
        $this->update([
            'investigation_completed_by' => $userId,
            'investigation_completed_at' => now(),
        ]);
    }

    /** Check if investigation has started */
    public function hasInvestigationStarted(): bool
    {
        return !empty($this->investigation_started_at);
    }

    /** Check if investigation is completed */
    public function isInvestigationCompleted(): bool
    {
        return !empty($this->investigation_completed_at);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->nomor_laporan)) {
                $model->nomor_laporan = self::generateNomorLaporan();
            }

            // Auto-sync timestamps dan user ID saat membuat record
            self::syncStatusTimestamps($model);
        });

        static::updating(function ($model) {
            // Auto-sync timestamps dan user ID saat status berubah
            if ($model->isDirty('status')) {
                self::syncStatusTimestamps($model);
            }
        });
    }

    /**
     * Sync timestamps dan user IDs berdasarkan status
     */
    protected static function syncStatusTimestamps($model): void
    {
        $currentUserId = auth()->id();

        switch ($model->status) {
            case self::STATUS_DILAPORKAN:
                // Set reported_at dan reported_by jika belum ada
                if (empty($model->reported_at)) {
                    $model->reported_at = now();
                }
                if (empty($model->reported_by) && $currentUserId) {
                    $model->reported_by = $currentUserId;
                }
                break;

            case self::STATUS_DIVERIFIKASI:
                // Set verified_at dan verified_by jika belum ada
                if (empty($model->verified_at)) {
                    $model->verified_at = now();
                }
                if (empty($model->verified_by) && $currentUserId) {
                    $model->verified_by = $currentUserId;
                }
                // Pastikan reported_at dan reported_by juga terisi (defensive)
                if (empty($model->reported_at)) {
                    $model->reported_at = now();
                }
                if (empty($model->reported_by) && $currentUserId) {
                    $model->reported_by = $currentUserId;
                }
                break;

            case self::STATUS_REVISI:
            case self::STATUS_REVISI_UNIT:
                // Set rejected_at dan rejected_by jika belum ada
                if (empty($model->rejected_at)) {
                    $model->rejected_at = now();
                }
                if (empty($model->rejected_by) && $currentUserId) {
                    $model->rejected_by = $currentUserId;
                }
                break;

            case self::STATUS_INVESTIGASI:
                // Pastikan reported_at, reported_by, verified_at, verified_by terisi
                if (empty($model->reported_at)) {
                    $model->reported_at = now();
                }
                if (empty($model->reported_by) && $currentUserId) {
                    $model->reported_by = $currentUserId;
                }
                if (empty($model->verified_at)) {
                    $model->verified_at = now();
                }
                if (empty($model->verified_by) && $currentUserId) {
                    $model->verified_by = $currentUserId;
                }
                break;
        }
    }

    public static function generateNomorLaporan(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastReport = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastReport ? intval(substr($lastReport->nomor_laporan, -4)) + 1 : 1;

        return sprintf('IKP/%s/%s/%04d', $year, $month, $sequence);
    }
}
