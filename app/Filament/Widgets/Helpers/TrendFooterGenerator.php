<?php

namespace App\Filament\Widgets\Helpers;

/**
 * Generate footer HTML dengan statistics untuk trend chart
 */
class TrendFooterGenerator
{
    /**
     * Generate footer HTML dengan stats
     *
     * @param array<string, mixed> $stats
     */
    public static function generate(array $stats): string
    {
        $total = (int) ($stats['total'] ?? 0);
        $peakMonthName = $stats['peakMonthName'] ?? '-';
        $peakValue = (int) ($stats['peakValue'] ?? 0);
        $increase = (float) ($stats['increase'] ?? 0);
        $decrease = (float) ($stats['decrease'] ?? 0);
        $monthsCount = (int) ($stats['monthsCount'] ?? 0);

        $cards = [];

        // Total tetap tampil
        $cards[] = "
            <div class='rounded-xl border border-gray-200 bg-white px-3 py-2 dark:border-gray-800 dark:bg-gray-900'>
                <p class='text-gray-500 dark:text-gray-400'>Total Laporan</p>
                <p class='text-base font-bold text-gray-900 dark:text-white'>{$total}</p>
            </div>
        ";

        // Peak tetap tampil jika ada data
        if ($peakValue > 0) {
            $cards[] = "
                <div class='rounded-xl border border-gray-200 bg-white px-3 py-2 dark:border-gray-800 dark:bg-gray-900'>
                    <p class='text-gray-500 dark:text-gray-400'>Puncak Insiden</p>
                    <p class='text-base font-bold text-danger-600'>
                        {$peakMonthName} ({$peakValue})
                    </p>
                </div>
            ";
        }

        // Trend hanya tampil jika data bulan > 1
        if ($monthsCount > 1) {

            if ($increase > 0) {
                $cards[] = "
                    <div class='rounded-xl border border-gray-200 bg-white px-3 py-2 dark:border-gray-800 dark:bg-gray-900'>
                        <p class='text-gray-500 dark:text-gray-400'>Kenaikan Tren</p>
                        <p class='text-base font-bold text-danger-600'>
                            +{$increase}%
                        </p>
                    </div>
                ";
            }

            if ($decrease > 0) {
                $cards[] = "
                    <div class='rounded-xl border border-gray-200 bg-white px-3 py-2 dark:border-gray-800 dark:bg-gray-900'>
                        <p class='text-gray-500 dark:text-gray-400'>Penurunan Tren</p>
                        <p class='text-base font-bold text-success-600'>
                            -{$decrease}%
                        </p>
                    </div>
                ";
            }
        }

        return "
            <div class='mt-4 flex flex-wrap items-center gap-3 text-xs'>
                " . implode('', $cards) . "
            </div>
        ";
    }
}
