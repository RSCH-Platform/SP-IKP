<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'kronologi',
        'insiden_terjadi_pada',
        'insiden_terjadi_pada_lainnya',
        'dampak_insiden',
        'kategori_insiden',
        'deskripsi_kategori_insiden',
        'tindakan_dilakukan',
        'grading_risiko',
        'catatan_tambahan',
        'status',
        // Workflow columns
        'reported_at',
        'verified_by',
        'verified_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'tanggal_lapor'   => 'date',
        'tanggal_insiden' => 'date',
        'tanggal_masuk_rs' => 'datetime',
        'reported_at'     => 'datetime',
        'verified_at'     => 'datetime',
        'rejected_at'     => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // --- Workflow transition methods ---

    /** Pelapor mengirim laporan ke kepala unit */
    public function submitLaporan(): void
    {
        $this->update([
            'status'      => self::STATUS_DILAPORKAN,
            'reported_at' => now(),
        ]);
    }

    /** Kepala unit memverifikasi (selesai grading & analisis), teruskan ke tim mutu */
    public function verifikasiLaporan(int $userId): void
    {
        $this->update([
            'status'      => self::STATUS_DIVERIFIKASI,
            'verified_by' => $userId,
            'verified_at' => now(),
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
            'status' => self::STATUS_INVESTIGASI,
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->nomor_laporan)) {
                $model->nomor_laporan = self::generateNomorLaporan();
            }
        });
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
