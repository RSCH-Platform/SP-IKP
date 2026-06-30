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

class NotifyKepalaUnitForNewReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public LaporanInsiden $laporan)
    {
    }

    public function handle(): void
    {
        $kepalaUnits = User::role('kepala_unit')->get();
        if ($kepalaUnits->isNotEmpty()) {
            Notification::make()
                ->title('Laporan Insiden Baru')
                ->body("Laporan baru ({$this->laporan->nomor_laporan}) telah dilaporkan oleh {$this->laporan->nama_pelapor}.")
                ->info()
                ->sendToDatabase($kepalaUnits);
        }
    }
}
