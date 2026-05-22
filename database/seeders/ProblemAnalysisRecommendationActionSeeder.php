<?php

namespace Database\Seeders;

use App\Models\IncidentProblem;
use App\Models\LaporanInsiden;
use App\Models\ProblemAction;
use App\Models\ProblemRecommendation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProblemAnalysisRecommendationActionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->resolveAdminUser();

        if (! $admin) {
            $this->command?->error('Tidak ada user admin_ikp, super_admin, atau user lain yang bisa dipakai sebagai default admin.');

            return;
        }

        $report = LaporanInsiden::query()
            ->with(['problems.recommendations', 'problems.actions'])
            ->where('status', LaporanInsiden::STATUS_INVESTIGASI)
            ->orderByDesc('investigation_started_at')
            ->orderByDesc('created_at')
            ->first();

        if (! $report) {
            $report = LaporanInsiden::query()
                ->orderByDesc('created_at')
                ->first();

            if (! $report) {
                $this->command?->error('Tidak ada laporan insiden untuk disiapkan. Jalankan seeder laporan terlebih dahulu.');

                return;
            }

            if (
                $report->status !== LaporanInsiden::STATUS_INVESTIGASI
                || empty($report->investigation_started_at)
                || empty($report->investigation_started_by)
            ) {
                LaporanInsiden::query()
                    ->whereKey($report->id)
                    ->update([
                        'status' => LaporanInsiden::STATUS_INVESTIGASI,
                        'investigation_started_by' => $admin->id,
                        'investigation_started_at' => now(),
                    ]);
            }
        } elseif (
            $report->status !== LaporanInsiden::STATUS_INVESTIGASI
            || empty($report->investigation_started_at)
            || empty($report->investigation_started_by)
        ) {
            LaporanInsiden::query()
                ->whereKey($report->id)
                ->update([
                    'status' => LaporanInsiden::STATUS_INVESTIGASI,
                    'investigation_started_by' => $admin->id,
                    'investigation_started_at' => now(),
                ]);
        }

        $problem = $report->problems()->first();

        if (! $problem) {
            $problem = $report->problems()->create([
                'problem_type' => 'SDP',
                'problem_description' => 'Analisis investigasi awal untuk laporan ini.',
                'created_by' => $admin->id,
            ]);
        }

        $recommendations = [
            [
                'recommendation_text' => 'Lakukan klarifikasi ulang kronologi kejadian kepada petugas terkait dan verifikasi alur dokumentasi.',
                'priority' => 'high',
            ],
            [
                'recommendation_text' => 'Perkuat checklist pemeriksaan sebelum tindakan agar potensi error dapat dicegah.',
                'priority' => 'medium',
            ],
        ];

        foreach ($recommendations as $recommendationData) {
            ProblemRecommendation::updateOrCreate(
                [
                    'problem_id' => $problem->id,
                    'recommendation_text' => $recommendationData['recommendation_text'],
                ],
                [
                    'priority' => $recommendationData['priority'],
                ]
            );
        }

        $actions = [
            [
                'action_text' => 'Menyusun analisis akar masalah bersama tim unit dan tim mutu.',
                'responsible_person' => 'admin',
                'deadline' => Carbon::now()->addDays(3),
                'status' => 'ongoing',
            ],
            [
                'action_text' => 'Melaksanakan briefing ulang kepada petugas terkait hasil investigasi awal.',
                'responsible_person' => 'admin',
                'deadline' => Carbon::now()->addDays(5),
                'status' => 'pending',
            ],
            [
                'action_text' => 'Mendokumentasikan bukti perbaikan dan menutup tindak lanjut awal.',
                'responsible_person' => 'admin',
                'deadline' => Carbon::now()->addDays(7),
                'status' => 'pending',
            ],
        ];

        foreach ($actions as $actionData) {
            ProblemAction::updateOrCreate(
                [
                    'problem_id' => $problem->id,
                    'action_text' => $actionData['action_text'],
                ],
                [
                    'responsible_person' => $actionData['responsible_person'],
                    'deadline' => $actionData['deadline'],
                    'status' => $actionData['status'],
                ]
            );
        }

        $this->command?->info(sprintf(
            '✅ Problem analysis seeded for laporan %s with %d recommendations and %d actions.',
            $report->nomor_laporan ?? 'unknown',
            count($recommendations),
            count($actions)
        ));
    }

    protected function resolveAdminUser(): ?User
    {
        $admin = User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['admin_ikp', 'super_admin']))
            ->first();

        if ($admin) {
            return $admin;
        }

        return User::query()->orderBy('id')->first();
    }
}