<?php

namespace App\Services;

use App\Models\LaporanInsiden;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class DashboardChartService
{
    protected const CACHE_TTL = 3600; // 1 hour

    /**
     * Get incident status distribution
     */
    public function getStatusDistribution()
    {
        return Cache::remember('chart:status-distribution', self::CACHE_TTL, function () {
            $data = LaporanInsiden::selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            return $this->formatStatusData($data);
        });
    }

    /**
     * Get incident category ranking (Top 8)
     */
    public function getCategoryRanking()
    {
        return Cache::remember('chart:category-ranking', self::CACHE_TTL, function () {
            $data = LaporanInsiden::selectRaw('kategori_insiden, COUNT(*) as total')
                ->whereNotNull('kategori_insiden')
                ->groupBy('kategori_insiden')
                ->orderByDesc('total')
                ->limit(8)
                ->pluck('total', 'kategori_insiden');

            return $this->formatCategoryData($data);
        });
    }

    /**
     * Get risk grading distribution
     */
    public function getRiskGradingDistribution()
    {
        return Cache::remember('chart:risk-grading', self::CACHE_TTL, function () {
            $data = LaporanInsiden::selectRaw('grading_risiko, COUNT(*) as total')
                ->whereNotNull('grading_risiko')
                ->groupBy('grading_risiko')
                ->pluck('total', 'grading_risiko');

            return $this->formatRiskGradingData($data);
        });
    }

    /**
     * Get monthly incident trend (last 12 months)
     */
    public function getMonthlyTrend()
    {
        return Cache::remember('chart:monthly-trend', self::CACHE_TTL, function () {
            $data = LaporanInsiden::selectRaw(
                'DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as total'
            )
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
                ->orderBy('month')
                ->get();

            return $this->formatMonthlyTrendData($data);
        });
    }

    /**
     * Format status data for chart
     */
    private function formatStatusData($data)
    {
        $statusLabels = [
            'draft' => 'Draft',
            'dilaporkan' => 'Dilaporkan',
            'revisi' => 'Revisi',
            'diverifikasi' => 'Diverifikasi',
            'revisi_unit' => 'Revisi Unit',
            'investigasi' => 'Investigasi',
        ];

        $statusColors = [
            'draft' => '#94a3b8',
            'dilaporkan' => '#3b82f6',
            'revisi' => '#f59e0b',
            'diverifikasi' => '#10b981',
            'revisi_unit' => '#f97316',
            'investigasi' => '#8b5cf6',
        ];

        return [
            'labels' => array_map(fn($status) => $statusLabels[$status] ?? $status, $data->keys()->toArray()),
            'series' => $data->values()->toArray(),
            'colors' => array_map(fn($status) => $statusColors[$status] ?? '#gray', $data->keys()->toArray()),
        ];
    }

    /**
     * Format category data for chart
     */
    private function formatCategoryData($data)
    {
        return [
            'labels' => $data->keys()->toArray(),
            'series' => $data->values()->toArray(),
        ];
    }

    /**
     * Format risk grading data for chart
     */
    private function formatRiskGradingData($data)
    {
        $riskOrder = [
            'Biru (Tidak signifikan)' => 1,
            'Hijau (Minor)' => 2,
            'Kuning (Moderat)' => 3,
            'Merah (Mayor)' => 4,
            'Hitam (Katastropik)' => 5,
        ];

        $riskColors = [
            'Biru (Tidak signifikan)' => '#0ea5e9',
            'Hijau (Minor)' => '#10b981',
            'Kuning (Moderat)' => '#eab308',
            'Merah (Mayor)' => '#ef4444',
            'Hitam (Katastropik)' => '#1f2937',
        ];

        // Sort by risk order
        $sortedData = collect($data)
            ->sortBy(fn($value, $key) => $riskOrder[$key] ?? 999)
            ->toArray();

        return [
            'labels' => array_keys($sortedData),
            'series' => array_values($sortedData),
            'colors' => array_map(fn($key) => $riskColors[$key] ?? '#gray', array_keys($sortedData)),
        ];
    }

    /**
     * Format monthly trend data for chart
     */
    private function formatMonthlyTrendData($data)
    {
        $months = [];
        $totals = [];

        foreach ($data as $item) {
            $months[] = $item->month;
            $totals[] = $item->total;
        }

        return [
            'months' => $months,
            'series' => $totals,
        ];
    }

    /**
     * Clear all chart caches
     */
    public static function clearCache()
    {
        Cache::forget('chart:status-distribution');
        Cache::forget('chart:category-ranking');
        Cache::forget('chart:risk-grading');
        Cache::forget('chart:monthly-trend');
    }
}
