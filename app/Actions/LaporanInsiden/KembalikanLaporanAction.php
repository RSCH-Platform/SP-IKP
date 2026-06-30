<?php

namespace App\Actions\LaporanInsiden;

use App\Models\LaporanInsiden;
use App\Jobs\NotifyPelaporForRevisionJob;

class KembalikanLaporanAction
{
    public function execute(LaporanInsiden $laporan, int $userId, string $reason, bool $toKepalaUnit = false): void
    {
        $status = $toKepalaUnit ? LaporanInsiden::STATUS_REVISI_UNIT : LaporanInsiden::STATUS_REVISI;
        $fromStatus = $laporan->status;

        $laporan->update([
            'status'           => $status,
            'rejected_by'      => $userId,
            'rejected_at'      => now(),
            'rejection_reason' => $reason,
        ]);

        \App\Models\LaporanInsidenTransition::create([
            'laporan_insiden_id' => $laporan->id,
            'actor_id' => $userId,
            'from_status' => $fromStatus,
            'to_status' => $status,
            'action_type' => 'rejected',
            'reason' => $reason,
        ]);

        if (!$toKepalaUnit) {
            NotifyPelaporForRevisionJob::dispatch($laporan, $reason);
        }
    }
}
