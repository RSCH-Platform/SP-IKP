<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LaporanInsiden;
use Illuminate\Support\Carbon;

class SignatureService
{
    /**
     * Generate HMAC-SHA256 signature for a report
     *
     * @param LaporanInsiden $laporan
     * @param array $additionalData Optional additional data to include in signature
     * @return string 64-character hex string
     */
    public static function generateSignature(
        LaporanInsiden $laporan,
        array $additionalData = []
    ): string {
        $payload = static::buildPayload($laporan, $additionalData);
        $payloadString = json_encode($payload, JSON_SORT_KEYS | JSON_UNESCAPED_SLASHES);

        return hash_hmac(
            'sha256',
            $payloadString,
            (string) config('app.key')
        );
    }

    /**
     * Verify signature integrity of a report
     *
     * @param LaporanInsiden $laporan
     * @param string $signature
     * @param array $additionalData Optional additional data that was included in signature
     * @return bool
     */
    public static function verifySignature(
        LaporanInsiden $laporan,
        string $signature,
        array $additionalData = []
    ): bool {
        if (empty($signature) || strlen($signature) !== 64) {
            return false;
        }

        $calculatedSignature = static::generateSignature($laporan, $additionalData);

        return hash_equals($signature, $calculatedSignature);
    }

    /**
     * Build payload for HMAC signature
     * Includes critical report data fields
     *
     * @param LaporanInsiden $laporan
     * @param array $additionalData
     * @return array
     */
    protected static function buildPayload(
        LaporanInsiden $laporan,
        array $additionalData = []
    ): array {
        $payload = [
            'report_id' => $laporan->id,
            'reporter_id' => $laporan->reported_by,
            'unit_kerja_id' => $laporan->unit_kerja_id,
            'status' => 'reported',
            'nama_pelapor' => $laporan->nama_pelapor,
            'jenis_insiden' => $laporan->jenis_insiden,
            'dampak_insiden' => $laporan->dampak_insiden,
            'deskripsi_kategori_insiden' => $laporan->deskripsi_kategori_insiden,
            'tindakan_dilakukan' => $laporan->tindakan_dilakukan,
            'lokasi_insiden' => $laporan->lokasi_insiden,
            'tanggal_insiden' => $laporan->tanggal_insiden?->toDateString(),
            'waktu_insiden' => $laporan->waktu_insiden,
            'timestamp' => now()->timestamp,
        ];

        // Merge additional data if provided
        if (!empty($additionalData)) {
            $payload = array_merge($payload, $additionalData);
        }

        return $payload;
    }

    /**
     * Generate data hash for integrity checking
     * Hash of serialized form data at confirmation time
     *
     * @param array $formData
     * @return string 64-character hex string
     */
    public static function generateDataHash(array $formData): string
    {
        $dataString = json_encode($formData, JSON_SORT_KEYS | JSON_UNESCAPED_SLASHES);

        return hash('sha256', $dataString);
    }

    /**
     * Verify data hasn't been tampered with
     *
     * @param array $currentData
     * @param string $storedHash
     * @return bool
     */
    public static function verifyDataIntegrity(array $currentData, string $storedHash): bool
    {
        $currentHash = static::generateDataHash($currentData);

        return hash_equals($currentHash, $storedHash);
    }

    /**
     * Create signature metadata
     *
     * @return array
     */
    public static function createMetadata(): array
    {
        return [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
            'timezone' => config('app.timezone'),
        ];
    }
}
