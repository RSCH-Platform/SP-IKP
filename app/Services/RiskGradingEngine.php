<?php

namespace App\Services;

class RiskGradingEngine
{
    /**
     * @return array{
     *  severity_score: int,
     *  severity_level: string,
     *  probability_score: int,
     *  probability_level: string,
     *  risk_score: int,
     *  risk_level: string,
     *  risk_band: string,
     *  required_action: string
     * }
     */
    public static function calculate(int $severity, int $probability): array
    {
        $severityMap = [
            1 => 'Tidak signifikan (Tidak ada cidera)',
            2 => 'Minor (Cidera ringan / dapat diatasi dengan pertolongan pertama)',
            3 => 'Moderat (Cedera sedang / gangguan fungsi reversibel / memperpanjang perawatan)',
            4 => 'Mayor (Cedera luas / kehilangan fungsi irreversibel)',
            5 => 'Katastropik (Kematian yang tidak berhubungan dengan perjalanan penyakit)'
        ];

        $probabilityMap = [
            1 => 'Sangat jarang / Rare (> 5 tahun/kali)',
            2 => 'Jarang / Unlikely (> 2–5 tahun/kali)',
            3 => 'Mungkin / Possible (1–2 tahun/kali)',
            4 => 'Sering / Likely (Beberapa kali/tahun)',
            5 => 'Sangat sering / Almost Certain (Tiap minggu/bulan)'
        ];

        $score = $severity * $probability;

        // Determine Risk Band explicitly per KKP matrix standard:
        // Blue: 1-3, Green: 4-6, Yellow: 7-12, Red: 15-25
        if ($score >= 15) {
            $level = 'Ekstrim';
            $band = 'Merah';
            $action = "Investigasi komprehensif / RCA\nMaksimal 45 hari\nTindakan segera\nPerhatian sampai ke Direktur";
        } elseif ($score >= 7) {
            $level = 'Tinggi';
            $band = 'Kuning';
            $action = "Investigasi komprehensif / RCA\nMaksimal 45 hari\nKaji dengan detail\nPerlu tindakan segera\nMembutuhkan perhatian Top Management / Direksi";
        } elseif ($score >= 4) {
            $level = 'Sedang';
            $band = 'Hijau';
            $action = "Investigasi sederhana\nMaksimal 2 minggu\nManager / pimpinan klinis menilai dampak terhadap biaya dan mengelola risiko";
        } else {
            $level = 'Rendah';
            $band = 'Biru';
            $action = "Investigasi sederhana";
        }

        return [
            'severity_score' => $severity,
            'severity_level' => $severityMap[$severity] ?? 'Unknown',
            'probability_score' => $probability,
            'probability_level' => $probabilityMap[$probability] ?? 'Unknown',
            'risk_score' => $score,
            'risk_level' => $level,
            'risk_band' => $band,
            'required_action' => $action,
        ];
    }
}
