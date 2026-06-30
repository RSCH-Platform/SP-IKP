<?php

namespace App\Actions\LaporanInsiden;

use App\Models\LaporanInsiden;
use App\Jobs\NotifyKepalaUnitForNewReportJob;

class SubmitLaporanAction
{
    public function execute(LaporanInsiden $laporan, int $userId): void
    {
        $laporan->update([
            'status'      => LaporanInsiden::STATUS_DILAPORKAN,
            'reported_by' => $userId,
            'reported_at' => now(),
        ]);

        \App\Models\LaporanInsidenTransition::create([
            'laporan_insiden_id' => $laporan->id,
            'actor_id' => $userId,
            'from_status' => LaporanInsiden::STATUS_DRAFT,
            'to_status' => LaporanInsiden::STATUS_DILAPORKAN,
            'action_type' => 'submitted',
        ]);

        NotifyKepalaUnitForNewReportJob::dispatch($laporan);
    }
}
