<?php

namespace App\Filament\Widgets\Helpers;

/**
 * Generate styled HTML untuk status filter yang dipisahkan
 */
class StatusFilterRenderer
{
    /**
     * Generate status filter HTML dengan style custom (menampilkan semua status)
     *
     * @param array<string> $selectedStatuses
     */
    public static function render(array $selectedStatuses = []): string
    {
        $statuses = [
            'draft' => 'Draft',
            'dilaporkan' => 'Dilaporkan',
            'revisi' => 'Perlu Revisi',
            'revisi_unit' => 'Revisi Unit',
            'diverifikasi' => 'Diverifikasi',
            'investigasi' => 'Investigasi',
            'selesai_investigasi' => 'Selesai Investigasi',
        ];

        $statusButtons = [];

        foreach ($statuses as $value => $label) {
            $isActive = in_array($value, $selectedStatuses);
            $bgColor = $isActive ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700';
            $borderColor = $isActive ? 'border-primary-500' : 'border-gray-200 dark:border-gray-700';

            $statusButtons[] = "
            <button
                type='button'
                wire:click=\"\\\$toggle('statusFilter', '{$value}')\"
                class='px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 border {$bgColor} border-{$borderColor}'>
                {$label}
            </button>
            ";
        }

        return "
        <div class='flex gap-2 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700'>
            <span class='text-xs font-semibold text-gray-600 dark:text-gray-400 self-center'>Status:</span>
            <div class='flex flex-wrap gap-2'>
                " . implode('', $statusButtons) . "
            </div>
        </div>
        ";
    }

    /**
     * Generate status filter dengan badge style (menampilkan semua status)
     *
     * @param array<string> $selectedStatuses
     */
    public static function renderBadge(array $selectedStatuses = []): string
    {
        $statuses = [
            'draft' => ['label' => 'Draft', 'icon' => '📝', 'color' => 'gray'],
            'dilaporkan' => ['label' => 'Dilaporkan', 'icon' => '📢', 'color' => 'sky'],
            'revisi' => ['label' => 'Perlu Revisi', 'icon' => '✏️', 'color' => 'red'],
            'revisi_unit' => ['label' => 'Revisi Unit', 'icon' => '⚙️', 'color' => 'amber'],
            'diverifikasi' => ['label' => 'Diverifikasi', 'icon' => '✓', 'color' => 'emerald'],
            'investigasi' => ['label' => 'Investigasi', 'icon' => '🔍', 'color' => 'violet'],
            'selesai_investigasi' => ['label' => 'Selesai Investigasi', 'icon' => '✅', 'color' => 'green'],
        ];

        $badges = [];

        foreach ($statuses as $value => $config) {
            $isActive = in_array($value, $selectedStatuses);

            $colorClass = match ($config['color']) {
                'gray' => $isActive ? 'bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-gray-100 ring-2 ring-gray-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                'sky' => $isActive ? 'bg-sky-200 text-sky-900 dark:bg-sky-700 dark:text-sky-100 ring-2 ring-sky-400' : 'bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400',
                'red' => $isActive ? 'bg-red-200 text-red-900 dark:bg-red-700 dark:text-red-100 ring-2 ring-red-400' : 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                'amber' => $isActive ? 'bg-amber-200 text-amber-900 dark:bg-amber-700 dark:text-amber-100 ring-2 ring-amber-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
                'emerald' => $isActive ? 'bg-emerald-200 text-emerald-900 dark:bg-emerald-700 dark:text-emerald-100 ring-2 ring-emerald-400' : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                'violet' => $isActive ? 'bg-violet-200 text-violet-900 dark:bg-violet-700 dark:text-violet-100 ring-2 ring-violet-400' : 'bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400',
                'green' => $isActive ? 'bg-green-200 text-green-900 dark:bg-green-700 dark:text-green-100 ring-2 ring-green-400' : 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
                default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
            };

            $cursorClass = $isActive ? 'cursor-default' : 'cursor-pointer hover:opacity-80';

            $badges[] = "
            <button
                type='button'
                wire:click=\"\\\$toggle('statusFilter', '{$value}')\"
                class='inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium transition-all {$colorClass} {$cursorClass}'>
                {$config['icon']} {$config['label']}
            </button>
            ";
        }

        return "
        <div class='flex flex-wrap gap-2'>
            " . implode('', $badges) . "
        </div>
        ";
    }

    /**
     * Generate status filter dengan card style (menampilkan semua status)
     *
     * @param array<string> $selectedStatuses
     */
    public static function renderCards(array $selectedStatuses = []): string
    {
        $statuses = [
            'draft' => [
                'label' => 'Draft',
                'icon' => '📝',
                'description' => 'Laporan dalam tahap penyusunan',
                'color' => 'bg-gray-50 border-gray-200 dark:bg-gray-900/20 dark:border-gray-800',
            ],
            'dilaporkan' => [
                'label' => 'Dilaporkan',
                'icon' => '📢',
                'description' => 'Laporan sudah dilaporkan',
                'color' => 'bg-sky-50 border-sky-200 dark:bg-sky-900/20 dark:border-sky-800',
            ],
            'revisi' => [
                'label' => 'Perlu Revisi',
                'icon' => '✏️',
                'description' => 'Laporan perlu direvisi',
                'color' => 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800',
            ],
            'revisi_unit' => [
                'label' => 'Revisi Unit',
                'icon' => '⚙️',
                'description' => 'Revisi dari unit kerja',
                'color' => 'bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:border-amber-800',
            ],
            'diverifikasi' => [
                'label' => 'Diverifikasi',
                'icon' => '✓',
                'description' => 'Laporan sudah diverifikasi',
                'color' => 'bg-emerald-50 border-emerald-200 dark:bg-emerald-900/20 dark:border-emerald-800',
            ],
            'investigasi' => [
                'label' => 'Investigasi',
                'icon' => '🔍',
                'description' => 'Laporan sedang diinvestigasi',
                'color' => 'bg-violet-50 border-violet-200 dark:bg-violet-900/20 dark:border-violet-800',
            ],
            'selesai_investigasi' => [
                'label' => 'Selesai Investigasi',
                'icon' => '✅',
                'description' => 'Investigasi sudah selesai',
                'color' => 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800',
            ],
        ];

        $cards = [];

        foreach ($statuses as $value => $config) {
            $isActive = in_array($value, $selectedStatuses);
            $checkmark = $isActive ? '✔' : '';
            $borderClass = $isActive ? 'ring-2 ring-primary-500' : '';

            $cards[] = "
            <div
                wire:click=\"\\\$toggle('statusFilter', '{$value}')\"
                class='p-4 rounded-lg border-2 cursor-pointer transition-all duration-200 {$config['color']} {$borderClass} hover:shadow-md'>
                <div class='flex items-start justify-between'>
                    <div class='flex items-start gap-3 flex-1'>
                        <span class='text-2xl'>{$config['icon']}</span>
                        <div>
                            <h4 class='font-semibold text-sm mb-1'>{$config['label']}</h4>
                            <p class='text-xs text-gray-600 dark:text-gray-400'>{$config['description']}</p>
                        </div>
                    </div>
                    " . ($isActive ? "<span class='text-primary-600 dark:text-primary-400 font-bold'>{$checkmark}</span>" : '') . "
                </div>
            </div>
            ";
        }

        return "
        <div class='grid grid-cols-1 sm:grid-cols-2 gap-3'>
            " . implode('', $cards) . "
        </div>
        ";
    }
}
