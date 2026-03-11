<?php

namespace App\Traits;

use App\Models\User;

trait HasWorkflowSteps
{
    public function getWorkflowSteps(): array
    {
        return [
            [
                'key' => 'draft',
                'title' => 'Draft',
                'desc' => 'Tahap awal pembuatan laporan',
                'icon' => 'heroicon-o-pencil',
                'message' => 'Pelapor dapat mengedit seluruh field sebelum laporan dikirim.',
                'by_key' => null,
                'date_key' => null,
            ],
            [
                'key' => 'dilaporkan',
                'title' => 'Dilaporkan',
                'desc' => 'Laporan dikirim ke kepala unit',
                'icon' => 'heroicon-o-paper-airplane',
                'message' => 'Menunggu proses verifikasi dari kepala unit.',
                'by_key' => 'reported_by',
                'date_key' => 'reported_at',
            ],
            [
                'key' => 'diverifikasi',
                'title' => 'Verifikasi',
                'desc' => 'Evaluasi kepala unit',
                'icon' => 'heroicon-o-shield-check',
                'message' => 'Grading risiko ditentukan pada tahap ini.',
                'by_key' => 'verified_by',
                'date_key' => 'verified_at',
            ],
            [
                'key' => 'investigasi',
                'title' => 'Investigasi',
                'desc' => 'Investigasi oleh tim mutu',
                'icon' => 'heroicon-o-magnifying-glass',
                'message' => 'Menentukan akar penyebab dan rekomendasi perbaikan.',
                'by_key' => null,
                'date_key' => null,
            ],
        ];
    }

    public function getStepDetail(array $step): string
    {
        $detail = $step['message'];

        // For draft step, show the reporter/creator
        if ($step['key'] === 'draft' && $this->record->user) {
            $userName = $this->record->user->name;
            $dateFormatted = $this->record->created_at->format('d F Y H:i');
            $detail .= "\n\n👤 Pelapor: {$userName}\n⏰ Tanggal: {$dateFormatted}";
        } elseif ($step['by_key'] && $step['date_key']) {
            $byId = $this->record->{$step['by_key']};
            $date = $this->record->{$step['date_key']};

            if ($byId && $date) {
                $user = User::find($byId);
                $userName = $user ? $user->name : 'Tidak diketahui';
                $dateFormatted = $date->format('d F Y H:i');

                $detail .= "\n\n👤 Oleh: {$userName}\n⏰ Tanggal: {$dateFormatted}";
            }
        }

        return $detail;
    }

    public function getStepStatus(string $stepKey, string $status): string
    {
        $order = [
            'draft',
            'dilaporkan',
            'diverifikasi',
            'investigasi'
        ];

        $currentIndex = array_search($status, $order);
        $stepIndex = array_search($stepKey, $order);

        if ($stepIndex < $currentIndex) {
            return 'done';
        }
        if ($stepIndex == $currentIndex) {
            return 'current';
        }
        return 'pending';
    }
}
