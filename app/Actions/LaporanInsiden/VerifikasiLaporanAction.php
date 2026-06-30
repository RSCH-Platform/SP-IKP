<?php

namespace App\Actions\LaporanInsiden;

use App\Models\LaporanInsiden;
use App\Jobs\NotifyTimMutuForInvestigationJob;
use Exception;

class VerifikasiLaporanAction
{
    public function execute(LaporanInsiden $laporan, int $userId, ?string $gradingRisiko = null, ?string $catatan = null): void
    {
        if ($gradingRisiko) {
            $laporan->grading_risiko = $gradingRisiko;
        }

        if ($catatan) {
            $laporan->catatan_tambahan = $catatan;
        }

        if ($laporan->grading_risiko === null) {
            throw new Exception('Laporan harus memiliki grading risiko untuk diverifikasi.');
        }

        $laporan->update([
            'status'      => LaporanInsiden::STATUS_DIVERIFIKASI,
            'verified_by' => $userId,
            'verified_at' => now(),
            'reported_by' => $laporan->reported_by ?? $userId,
            'reported_at' => $laporan->reported_at ?? now(),
        ]);

        \App\Models\Investigation::updateOrCreate(
            ['laporan_insiden_id' => $laporan->id],
            [
                'grading_risiko' => $laporan->grading_risiko,
                'status' => 'pending',
            ]
        );

        \App\Models\LaporanInsidenTransition::create([
            'laporan_insiden_id' => $laporan->id,
            'actor_id' => $userId,
            'from_status' => LaporanInsiden::STATUS_DILAPORKAN,
            'to_status' => LaporanInsiden::STATUS_DIVERIFIKASI,
            'action_type' => 'verified',
            'reason' => $catatan,
        ]);

        NotifyTimMutuForInvestigationJob::dispatch($laporan);
    }
}
