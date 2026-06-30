<?php

namespace App\Actions\LaporanInsiden;

use App\Models\LaporanInsiden;

class MulaiInvestigasiAction
{
    public function execute(LaporanInsiden $laporan, int $userId): void
    {
        $fromStatus = $laporan->status;

        $laporan->update([
            'status'      => LaporanInsiden::STATUS_INVESTIGASI,
            'reported_by' => $laporan->reported_by ?? $userId,
            'reported_at' => $laporan->reported_at ?? now(),
        ]);

        \App\Models\Investigation::updateOrCreate(
            ['laporan_insiden_id' => $laporan->id],
            [
                'status' => 'in_progress',
                'investigation_started_by' => $userId,
                'investigation_started_at' => now(),
            ]
        );

        \App\Models\LaporanInsidenTransition::create([
            'laporan_insiden_id' => $laporan->id,
            'actor_id' => $userId,
            'from_status' => $fromStatus,
            'to_status' => LaporanInsiden::STATUS_INVESTIGASI,
            'action_type' => 'investigation_started',
        ]);
    }
}
