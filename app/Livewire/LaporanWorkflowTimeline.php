<?php

namespace App\Livewire;

use Livewire\Component;

class LaporanWorkflowTimeline extends Component
{
    public $record;
    public $workflowHistory;

    public function mount($record, $workflowHistory = null)
    {
        $this->record = $record;
        $this->workflowHistory = $workflowHistory;
    }

    private function getColorStyles($color)
    {
        $colorMap = [
            'green' => [
                'bg' => '#dcfce7',
                'text' => '#15803d',
                'badge_bg' => '#dcfce7',
                'badge_text' => '#166534',
                'dark_bg' => 'rgba(34, 197, 94, 0.1)',
                'dark_text' => '#86efac',
                'dark_badge_bg' => 'rgba(34, 197, 94, 0.1)',
                'dark_badge_text' => '#86efac',
            ],
            'yellow' => [
                'bg' => '#fef3c7',
                'text' => '#b45309',
                'badge_bg' => '#fef3c7',
                'badge_text' => '#92400e',
                'dark_bg' => 'rgba(234, 179, 8, 0.1)',
                'dark_text' => '#facc15',
                'dark_badge_bg' => 'rgba(234, 179, 8, 0.1)',
                'dark_badge_text' => '#facc15',
            ],
            'blue' => [
                'bg' => '#dbeafe',
                'text' => '#0c4a6e',
                'badge_bg' => '#dbeafe',
                'badge_text' => '#082f49',
                'dark_bg' => 'rgba(59, 130, 246, 0.1)',
                'dark_text' => '#93c5fd',
                'dark_badge_bg' => 'rgba(59, 130, 246, 0.1)',
                'dark_badge_text' => '#93c5fd',
            ]
        ];

        return $colorMap[$color] ?? $colorMap['green'];
    }

    public function getProgressProperty()
    {
        $status = $this->record->status;
        $order = ['draft', 'dilaporkan', 'diverifikasi', 'investigasi'];

        return [
            'current' => $status,
            'steps' => $order
        ];
    }

    public function stepStatus($stepKey)
    {
        $order = ['draft', 'dilaporkan', 'diverifikasi', 'investigasi'];
        $currentIndex = array_search($this->record->status, $order);
        $stepIndex = array_search($stepKey, $order);

        if ($stepIndex < $currentIndex) return 'done';
        if ($stepIndex === $currentIndex) return 'current';
        return 'pending';
    }

    public function getStepsProperty()
    {
        return [
            [
                'show' => true,
                'key' => 'draft',
                'title' => 'Draft',
                'desc' => 'Tahap awal pembuatan laporan',
                'icon' => 'heroicon-o-pencil',
                'color' => 'green',
                'styles' => $this->getColorStyles('green'),
                'data' => [
                    'Pembuat Laporan' => $this->record->user?->name ?? '-',
                    'Dibuat pada' => $this->record->created_at?->translatedFormat('d F Y, H:i') ?? '-',
                ]
            ],

            [
                'show' => in_array($this->record->status, ['dilaporkan', 'diverifikasi', 'investigasi']),
                'key' => 'dilaporkan',
                'title' => 'Dilaporkan',
                'desc' => 'Laporan dikirim ke kepala unit',
                'icon' => 'heroicon-o-paper-airplane',
                'color' => 'green',
                'styles' => $this->getColorStyles('green'),
                'data' => [
                    'Pelapor' => $this->record->nama_pelapor ?? '-',
                    'Dikirim pada' => $this->record->reported_at?->translatedFormat('d F Y, H:i') ?? '-',
                ]
            ],

            [
                'show' => in_array($this->record->status, ['diverifikasi', 'investigasi']),
                'key' => 'diverifikasi',
                'title' => 'Verifikasi',
                'desc' => 'Evaluasi kepala unit',
                'icon' => 'heroicon-o-shield-check',
                'color' => 'green',
                'styles' => $this->getColorStyles('green'),
                'data' => [
                    'Verifikator' => $this->record->verifier?->name ?? '-',
                    'Diverifikasi pada' => $this->record->verified_at?->translatedFormat('d F Y, H:i') ?? '-',
                    'Grading Risiko' => $this->record->grading_risiko ?? '-'
                ]
            ],

            [
                'show' => $this->record->status === 'investigasi',
                'key' => 'investigasi',
                'title' => 'Investigasi',
                'desc' => 'Investigasi oleh tim mutu',
                'icon' => 'heroicon-o-magnifying-glass',
                'color' => 'blue',
                'styles' => $this->getColorStyles('blue'),
                'badge' => 'Status Saat Ini',
                'data' => [
                    'Tim Investigator' => 'Tim Mutu',
                    'Status' => 'Dalam Proses'
                ]
            ]
        ];
    }

    public function render()
    {
        return view('livewire.laporan-workflow-timeline', [
            'steps' => $this->steps,
        ]);
    }
}
