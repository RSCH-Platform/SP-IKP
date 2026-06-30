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

class NotifyTimMutuForInvestigationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public LaporanInsiden $laporan)
    {
    }

    public function handle(): void
    {
        $timMutu = User::role(['tim_mutu', 'admin_ikp'])->get();

        if ($timMutu->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('Laporan Siap Investigasi')
            ->body("Laporan dari {$this->laporan->nama_pelapor} ({$this->laporan->nomor_laporan}) telah diverifikasi dan siap untuk diinvestigasi.")
            ->info()
            ->sendToDatabase($timMutu);
    }
}
