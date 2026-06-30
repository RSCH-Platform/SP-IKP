<?php

namespace App\Jobs;

use App\Models\LaporanInsiden;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyPelaporForRevisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public LaporanInsiden $laporan, public string $reason)
    {
    }

    public function handle(): void
    {
        $pelapor = User::find($this->laporan->created_by);

        if (!$pelapor) {
            return;
        }

        Notification::make()
            ->title('Laporan Dikembalikan untuk Revisi')
            ->body("Laporan Anda ({$this->laporan->nomor_laporan}) perlu diperbaiki. Alasan: {$this->reason}")
            ->warning()
            ->sendToDatabase($pelapor);
    }
}
