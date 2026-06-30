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
                'key' => 'investigasi_start',
                'title' => 'Mulai Investigasi',
                'desc' => 'Investigasi dimulai oleh tim mutu & kepala unit',
                'icon' => 'heroicon-o-play-circle',
                'message' => 'Tim mutu mulai melakukan pengumpulan data investigasi.',
                'by_key' => 'investigation_started_by',
                'date_key' => 'investigation_started_at',
            ],
            [
                'key' => 'investigasi_complete',
                'title' => 'Investigasi Selesai',
                'desc' => 'Investigasi selesai oleh tim mutu & kepala unit',
                'icon' => 'heroicon-o-check-circle',
                'message' => 'Tim mutu telah menyelesaikan investigasi dan menentukan akar penyebab serta rekomendasi perbaikan.',
                'by_key' => 'investigation_completed_by',
                'date_key' => 'investigation_completed_at',
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
        } elseif ($step['key'] === 'investigasi_start') {
            // Show investigation start details
            if ($this->record->investigation_started_by && $this->record->investigation_started_at) {
                $user = $this->record->investigationStarter ?? clone User::find($this->record->investigation_started_by);
                $userName = $user ? $user->name : 'Tidak diketahui';
                $dateFormatted = $this->record->investigation_started_at->format('d F Y H:i');
                $detail .= "\n\n👤 Oleh: {$userName}\n⏰ Tanggal: {$dateFormatted}";
            }
        } elseif ($step['key'] === 'investigasi_complete') {
            // Show investigation complete details
            if ($this->record->investigation_completed_by && $this->record->investigation_completed_at) {
                $user = $this->record->investigationCompleter ?? clone User::find($this->record->investigation_completed_by);
                $userName = $user ? $user->name : 'Tidak diketahui';
                $dateFormatted = $this->record->investigation_completed_at->format('d F Y H:i');
                $detail .= "\n\n👤 Oleh: {$userName}\n⏰ Tanggal: {$dateFormatted}";
            }
        } elseif ($step['by_key'] && $step['date_key']) {
            $byId = $this->record->{$step['by_key']};
            $date = $this->record->{$step['date_key']};

            if ($byId && $date) {
                $user = null;
                if ($step['by_key'] === 'reported_by') {
                    $user = $this->record->reporter;
                } elseif ($step['by_key'] === 'verified_by') {
                    $user = $this->record->verifier;
                } elseif ($step['by_key'] === 'rejected_by') {
                    $user = $this->record->rejecter;
                }
                
                $user = $user ?? User::find($byId);
                $userName = $user ? $user->name : 'Tidak diketahui';
                $dateFormatted = $date->format('d F Y H:i');

                $detail .= "\n\n👤 Oleh: {$userName}\n⏰ Tanggal: {$dateFormatted}";
            }
        }

        return $detail;
    }

    public function getStepStatus(string $stepKey, string $status): string
    {
        // Map step keys to their completion logic using timestamps
        switch ($stepKey) {
            case 'draft':
                return $status === 'draft' ? 'current' : 'done';

            case 'dilaporkan':
                if ($status === 'dilaporkan') {
                    return 'current';
                }
                return in_array($status, ['revisi', 'diverifikasi', 'revisi_unit', 'investigasi'], true)
                    ? 'done'
                    : 'pending';

            case 'diverifikasi':
                if ($status === 'diverifikasi') {
                    return 'current';
                }
                return in_array($status, ['revisi_unit', 'investigasi'], true)
                    ? 'done'
                    : 'pending';

            case 'investigasi_start':
                // Step becomes current once investigation has started but not yet completed
                if ($this->record->investigation_started_at && !$this->record->investigation_completed_at) {
                    return 'current';
                }
                return $this->record->investigation_started_at ? 'done' : 'pending';

            case 'investigasi_complete':
                // Completed step is considered done once timestamp exists
                return $this->record->investigation_completed_at ? 'done' : 'pending';

            default:
                return 'pending';
        }
    }
}
