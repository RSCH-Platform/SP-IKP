<?php

namespace App\Actions\LaporanInsiden;

use App\Models\LaporanInsiden;

class ReopenInvestigasiAction
{
    public function execute(LaporanInsiden $laporan, int $userId): void
    {
        $fromStatus = $laporan->status;

        $laporan->update([
            'status' => LaporanInsiden::STATUS_INVESTIGASI,
        ]);

        \App\Models\Investigation::updateOrCreate(
            ['laporan_insiden_id' => $laporan->id],
            [
                'status' => 'in_progress',
                'investigation_completed_by' => null,
                'investigation_completed_at' => null,
            ]
        );

        \App\Models\LaporanInsidenTransition::create([
            'laporan_insiden_id' => $laporan->id,
            'actor_id' => $userId,
            'from_status' => $fromStatus,
            'to_status' => LaporanInsiden::STATUS_INVESTIGASI,
            'action_type' => 'investigation_reopened',
        ]);
    }
}
