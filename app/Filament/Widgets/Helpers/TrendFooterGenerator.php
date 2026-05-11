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
        $total = $stats['total'] ?? 0;
        $average = $stats['average'] ?? 0;
        $peakMonthName = $stats['peakMonthName'] ?? '-';
        $peakValue = $stats['peakValue'] ?? 0;
        $increase = $stats['increase'] ?? 0;
        $decrease = $stats['decrease'] ?? 0;

        return "
        <div class='flex flex-wrap items-center gap-3 mt-4 text-xs'>
            <div class='rounded-xl border border-gray-200 dark:border-gray-800 px-3 py-2 bg-white dark:bg-gray-900'>
                <p class='text-gray-500 dark:text-gray-400'>Total Laporan</p>
                <p class='text-base font-bold text-gray-900 dark:text-white'>{$total}</p>
            </div>

            <div class='rounded-xl border border-gray-200 dark:border-gray-800 px-3 py-2 bg-white dark:bg-gray-900'>
                <p class='text-gray-500 dark:text-gray-400'>Rata-rata</p>
                <p class='text-base font-bold text-primary-600'>{$average}/bulan</p>
            </div>

            <div class='rounded-xl border border-gray-200 dark:border-gray-800 px-3 py-2 bg-white dark:bg-gray-900'>
                <p class='text-gray-500 dark:text-gray-400'>Puncak Insiden</p>
                <p class='text-base font-bold text-danger-600'>{$peakMonthName} ({$peakValue})</p>
            </div>

            <div class='rounded-xl border border-gray-200 dark:border-gray-800 px-3 py-2 bg-white dark:bg-gray-900'>
                <p class='text-gray-500 dark:text-gray-400'>Kenaikan Tren Insiden</p>
                <p class='text-base font-bold text-danger-600'>
                    +{$increase}%
                </p>
            </div>

            <div class='rounded-xl border border-gray-200 dark:border-gray-800 px-3 py-2 bg-white dark:bg-gray-900'>
                <p class='text-gray-500 dark:text-gray-400'>Penurunan Tren Insiden</p>
                <p class='text-base font-bold text-success-600'>
                    -{$decrease}%
                </p>
            </div>
        </div>
    ";
    }
}
