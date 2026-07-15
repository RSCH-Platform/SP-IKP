<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas;

use App\Models\LaporanInsiden;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class LaporanInsidenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Review Laporan Insiden')
                        ->key('review-laporan-insiden')

                        /*
                         * Step review hanya boleh diedit ketika:
                         * - laporan masih Draft, atau
                         * - laporan sudah Selesai, atau
                         * - user punya izin khusus ForceEdit.
                         *
                         * Selain kondisi itu, step dikunci agar laporan yang sudah masuk alur
                         * verifikasi / investigasi tidak sembarang diubah.
                         * 
                         * dan ketika laporan sudah Selesai, step tetap bisa dilihat tapi tidak diedit, untuk menjaga integritas data laporan yang sudah final.
                         */
                        ->disabled(
                            fn($record, string $context) =>
                            $context === 'create'
                            ? false
                            : (
                                $record?->status === LaporanInsiden::STATUS_SELESAI
                                ? true
                                : (
                                    $record?->status === LaporanInsiden::STATUS_DRAFT
                                    ? false
                                    : !Auth::user()?->can('ForceEdit:LaporanInsiden')
                                )
                            )
                        )
                        ->schema([
                            /*
                             * Section pelapor hanya bisa diedit oleh pelapor asli,
                             * kecuali user memiliki izin ForceEdit.
                             *
                             * Tujuannya agar identitas/data pelapor tidak diubah oleh user lain.
                             */
                            LaporanInsidenFormSchema::sectionPelapor()
                                ->disabled(
                                    fn($record, string $context) =>
                                    $context !== 'create' && (
                                        !Auth::user()?->can('ForceEdit:LaporanInsiden')
                                        || Auth::id() !== $record?->user_id
                                    )
                                ),

                            LaporanInsidenFormSchema::sectionPasien(),

                            /*
                             * Section insiden memiliki dua mode:
                             * - true  : untuk laporan yang sudah melewati tahap awal
                             * - false : untuk Draft / Dilaporkan
                             *
                             * Ini menjaga tampilan form tetap sesuai dengan status laporan.
                             */
                            LaporanInsidenFormSchema::sectionInsiden(true)
                                ->visible(
                                    fn($record) =>
                                    $record !== null && !in_array($record?->status, array_merge(
                                        LaporanInsiden::TAHAP_AWAL,
                                        LaporanInsiden::TAHAP_GRADING
                                    ))
                                ),

                            LaporanInsidenFormSchema::sectionInsiden(false)
                                ->visible(
                                    fn($record) =>
                                    $record === null || in_array($record?->status, array_merge(
                                        LaporanInsiden::TAHAP_AWAL,
                                        LaporanInsiden::TAHAP_GRADING
                                    ))
                                ),

                            LaporanInsidenFormSchema::sectionKronologi(collapsed: false),

                            LaporanInsidenFormSchema::sectionTindakan(collapsed: false),
                            // (sectionGradingResiko dihapus dari sini karena sekarang akan selalu ada di Step 2 secara berkesinambungan)

                            // LaporanInsidenFormSchema::sectionCatatanTambahan()
                            //     ->hidden(fn ($record) => ! ($record?->status !== LaporanInsiden::STATUS_DRAFT)),
                        ]),

                    Step::make('Grading Resiko & Catatan Tambahan')
                        ->key('grading-resiko-catatan-tambahan')

                        /*
                         * Step grading risiko akan muncul setelah tahap awal lewat.
                         * Tetap dimunculkan di tahap verifikasi & investigasi sebagai read-only history.
                         */
                        ->visible(
                            fn($record) =>
                            $record !== null && in_array($record?->status, LaporanInsiden::FLOW_PASCA_AWAL)
                        )

                        /*
                         * Step hanya bisa diedit di tahap grading (Dilaporkan, Revisi Unit).
                         * Walaupun secara visible sudah dibatasi, disabled tetap dipakai
                         * sebagai pengaman agar step tidak bisa diedit di status lain.
                         */
                        ->disabled(
                            fn($record) =>
                            $record?->status === LaporanInsiden::STATUS_SELESAI
                        )
                        ->schema([
                            LaporanInsidenFormSchema::sectionGradingResiko(),
                            LaporanInsidenFormSchema::sectionCatatanTambahan(),
                        ]),

                    Step::make('Pengumpulan Data')
                        ->key('pengumpulan-data')

                        /*
                         * Step pengumpulan data hanya ditampilkan jika:
                         * - user punya izin Investigasi,
                         * - status laporan sedang Investigasi atau sudah Selesai,
                         * - investigasi benar-benar sudah dimulai.
                         *
                         * Ini mencegah form investigasi muncul sebelum proses investigasi dimulai.
                         */
                        ->visible(
                            fn($record) =>
                            Auth::user()?->can('Investigasi:LaporanInsiden')
                            && $record !== null && in_array($record?->status, LaporanInsiden::FLOW_INVESTIGASI_DAN_SELESAI)
                            && $record->hasInvestigationStarted()
                        )

                        /*
                         * Step hanya bisa diedit saat status masih Investigasi.
                         * Jika laporan sudah Selesai, data tetap bisa dilihat tapi tidak diedit.
                         */
                        ->disabled(
                            fn($record) =>
                            $record?->status === LaporanInsiden::STATUS_SELESAI
                        )
                        ->schema([
                            LaporanInsidenFormSchema::getFieldDataCollection(),
                        ]),

                    Step::make('Tabular Timeline')
                        ->key('tabular-timeline')

                        /*
                         * Timeline mengikuti aturan yang sama dengan pengumpulan data:
                         * hanya muncul untuk user investigasi, status investigasi/selesai,
                         * dan investigasi sudah dimulai.
                         */
                        ->visible(
                            fn($record) =>
                            Auth::user()?->can('Investigasi:LaporanInsiden')
                            && $record !== null && in_array($record?->status, LaporanInsiden::FLOW_INVESTIGASI_DAN_SELESAI)
                            && $record->hasInvestigationStarted()
                        )

                        /*
                         * Timeline hanya bisa diedit ketika investigasi masih berjalan.
                         * Saat laporan selesai, timeline menjadi read-only.
                         */
                        ->disabled(
                            fn($record) =>
                            $record?->status === LaporanInsiden::STATUS_SELESAI
                        )
                        ->schema([
                            LaporanInsidenFormSchema::getFieldTimelineGrid(collapsed: false),

                            // OLD DESIGN, disimpan sementara sebagai referensi:
                            // LaporanInsidenFormSchema::getFieldTabularTimeline(),
                        ]),

                    Step::make('Analisa Masalah')
                        ->key('analisa-masalah')

                        /*
                         * Analisa masalah hanya relevan setelah investigasi dimulai.
                         * Karena itu, syarat tampilnya sama dengan step investigasi lainnya.
                         */
                        ->visible(
                            fn($record) =>
                            Auth::user()?->can('Investigasi:LaporanInsiden')
                            && $record !== null && in_array($record?->status, LaporanInsiden::FLOW_INVESTIGASI_DAN_SELESAI)
                            && $record->hasInvestigationStarted()
                        )

                        /*
                         * Analisa masalah hanya dapat diubah selama status masih Investigasi.
                         * Setelah laporan Selesai, data analisa dikunci.
                         */
                        ->disabled(
                            fn($record) =>
                            $record?->status === LaporanInsiden::STATUS_SELESAI
                        )
                        ->schema([
                            LaporanInsidenFormSchema::getFieldProblemAnalysisOptimize(),
                        ]),
                ])
                    ->persistStepInQueryString(),
            ])
            ->columns(1);
    }
}