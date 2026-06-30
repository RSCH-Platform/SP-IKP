<?php

namespace App\Actions\LaporanInsiden;

use App\Models\LaporanInsiden;

class SelesaikanInvestigasiAction
{
    public function execute(LaporanInsiden $laporan, int $userId): void
    {
        $fromStatus = $laporan->status;

        $laporan->update([
            'status' => LaporanInsiden::STATUS_SELESAI,
        ]);

        \App\Models\Investigation::updateOrCreate(
            ['laporan_insiden_id' => $laporan->id],
            [
                'status' => 'completed',
                'investigation_completed_by' => $userId,
                'investigation_completed_at' => now(),
            ]
        );

        \App\Models\LaporanInsidenTransition::create([
            'laporan_insiden_id' => $laporan->id,
            'actor_id' => $userId,
            'from_status' => $fromStatus,
            'to_status' => LaporanInsiden::STATUS_SELESAI,
            'action_type' => 'investigation_completed',
        ]);
    }
}
