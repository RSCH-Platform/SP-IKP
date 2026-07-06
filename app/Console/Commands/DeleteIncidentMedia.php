<?php

namespace App\Console\Commands;

use App\Models\LaporanInsiden;
use Illuminate\Console\Command;

class DeleteIncidentMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:delete-incident 
                            {identifier : Nomor Laporan (contoh: IKP/2026/05/0049) atau ID Laporan} 
                            {--id : Gunakan flag ini jika identifier yang dimasukkan adalah ID (angka)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus semua file media yang terhubung ke sebuah Laporan Insiden beserta relasinya';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $identifier = $this->argument('identifier');
        $isId = $this->option('id');

        $this->info("Mencari Laporan Insiden berdasarkan " . ($isId ? "ID" : "Nomor Laporan") . " : {$identifier}");

        if ($isId) {
            $laporan = LaporanInsiden::find($identifier);
        } else {
            $laporan = LaporanInsiden::where('nomor_laporan', $identifier)->first();
        }

        if (!$laporan) {
            $this->error("Laporan Insiden tidak ditemukan!");
            return 1;
        }

        $this->info("Laporan ditemukan: {$laporan->nomor_laporan} (ID: {$laporan->id})");
        $this->line("Sedang mengumpulkan data media dari laporan dan relasinya...\n");

        $mediaItems = collect();

        // 1. Media pada Laporan Utama
        $mediaItems = $mediaItems->merge($laporan->media);

        // 2. Media pada Investigation Data
        if ($laporan->investigationData) {
            foreach ($laporan->investigationData as $data) {
                $mediaItems = $mediaItems->merge($data->media);
            }
        }

        // 3. Media pada Problem & Problem Action
        if ($laporan->problems) {
            foreach ($laporan->problems as $problem) {
                if (method_exists($problem, 'media')) {
                    $mediaItems = $mediaItems->merge($problem->media);
                }
                if ($problem->actions) {
                    foreach ($problem->actions as $action) {
                        $mediaItems = $mediaItems->merge($action->media);
                    }
                }
            }
        }

        // 4. Media pada Timeline Entries
        if ($laporan->timelineEntries) {
            foreach ($laporan->timelineEntries as $entry) {
                if (method_exists($entry, 'media')) {
                    $mediaItems = $mediaItems->merge($entry->media);
                }
            }
        }

        if ($mediaItems->isEmpty()) {
            $this->warn("TIDAK ADA file media yang ditemukan pada laporan ini maupun relasinya.");
            return 0;
        }

        // Tampilkan rincian media yang akan dihapus
        $this->info("Ditemukan {$mediaItems->count()} file media:");
        $tableData = $mediaItems->map(function ($media) {
            return [
                'ID Media' => $media->id,
                'File Name' => $media->file_name,
                'Model' => class_basename($media->model_type),
                'Size (KB)' => round($media->size / 1024, 2)
            ];
        })->toArray();

        $this->table(['ID Media', 'File Name', 'Terkait Dengan Model', 'Ukuran (KB)'], $tableData);

        $this->warn("\nPERINGATAN: Aksi ini akan menghapus permanen record media dari database beserta FILE FISIKNYA dari storage (Local/S3).");
        
        if ($this->confirm('Apakah Anda yakin ingin melanjutkan proses penghapusan ini?', false)) {
            $this->line("Sedang menghapus file...");
            $bar = $this->output->createProgressBar($mediaItems->count());
            $bar->start();

            $success = 0;
            $failed = 0;

            foreach ($mediaItems as $media) {
                try {
                    // Menggunakan delete() instance agar trigger observer Spatie Media Library (menghapus file fisiknya juga)
                    $media->delete(); 
                    $success++;
                } catch (\Exception $e) {
                    $this->error("\nGagal menghapus Media ID {$media->id}: " . $e->getMessage());
                    $failed++;
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            $this->info("Proses selesai!");
            $this->info("Berhasil dihapus: {$success} file");
            if ($failed > 0) {
                $this->error("Gagal dihapus: {$failed} file");
            }
        } else {
            $this->info("Proses dibatalkan. Tidak ada file yang dihapus.");
        }

        return 0;
    }
}
