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
                            fn($record) =>
                            $record->status === LaporanInsiden::STATUS_SELESAI
                            ? true
                            : (
                                $record->status === LaporanInsiden::STATUS_DRAFT
                                ? false
                                : !Auth::user()?->can('ForceEdit:LaporanInsiden')
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
                                    fn($record) =>
                                    !Auth::user()?->can('ForceEdit:LaporanInsiden')
                                    || Auth::id() !== $record->pelapor_id
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
                                    !in_array($record->status, [
                                        LaporanInsiden::STATUS_DRAFT,
                                        LaporanInsiden::STATUS_DILAPORKAN,
                                    ])
                                ),

                            LaporanInsidenFormSchema::sectionInsiden(false)
                                ->visible(
                                    fn($record) =>
                                    in_array($record->status, [
                                        LaporanInsiden::STATUS_DRAFT,
                                        LaporanInsiden::STATUS_DILAPORKAN,
                                    ])
                                ),

                            LaporanInsidenFormSchema::sectionKronologi(collapsed: false),

                            LaporanInsidenFormSchema::sectionTindakan(collapsed: false),

                            /*
                             * Grading risiko di halaman review baru ditampilkan setelah laporan
                             * melewati status Draft, Dilaporkan, dan Diverifikasi.
                             *
                             * Artinya grading ini ditampilkan untuk tahap lanjutan,
                             * bukan saat laporan masih awal atau baru diverifikasi.
                             */
                            LaporanInsidenFormSchema::sectionGradingResiko()
                                ->visible(
                                    fn($record) =>
                                    !in_array($record->status, [
                                        LaporanInsiden::STATUS_DRAFT,
                                        LaporanInsiden::STATUS_DILAPORKAN,
                                        LaporanInsiden::STATUS_DIVERIFIKASI,
                                    ])
                                ),

                            // LaporanInsidenFormSchema::sectionCatatanTambahan()
                            //     ->hidden(fn ($record) => ! ($record->status !== LaporanInsiden::STATUS_DRAFT)),
                        ]),

                    Step::make('Grading Resiko & Catatan Tambahan')
                        ->key('grading-resiko-catatan-tambahan')

                        /*
                         * Step ini khusus untuk laporan berstatus Dilaporkan.
                         *
                         * Pada tahap ini Validator / Tim IKP dapat memberi grading risiko
                         * dan catatan tambahan sebelum laporan naik ke proses berikutnya.
                         */
                        ->hidden(
                            fn($record) =>
                            !in_array($record->status, [
                                LaporanInsiden::STATUS_DILAPORKAN,
                            ])
                        )

                        /*
                         * Step dikunci jika status bukan Dilaporkan.
                         *
                         * Walaupun secara hidden sudah dibatasi, disabled tetap dipakai
                         * sebagai pengaman agar step tidak bisa diedit di status lain.
                         */
                        ->disabled(
                            fn($record) =>
                            $record->status !== LaporanInsiden::STATUS_DILAPORKAN
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
                        ->hidden(
                            fn($record) =>
                            !(
                                Auth::user()?->can('Investigasi:LaporanInsiden')
                                && in_array($record->status, [
                                    LaporanInsiden::STATUS_INVESTIGASI,
                                    LaporanInsiden::STATUS_SELESAI,
                                ])
                                && $record->investigation_started_by !== null
                            )
                        )

                        /*
                         * Step hanya bisa diedit saat status masih Investigasi.
                         * Jika laporan sudah Selesai, data tetap bisa dilihat tapi tidak diedit.
                         */
                        ->disabled(
                            fn($record) =>
                            $record->status !== LaporanInsiden::STATUS_INVESTIGASI
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
                        ->hidden(
                            fn($record) =>
                            !(
                                Auth::user()?->can('Investigasi:LaporanInsiden')
                                && in_array($record->status, [
                                    LaporanInsiden::STATUS_INVESTIGASI,
                                    LaporanInsiden::STATUS_SELESAI,
                                ])
                                && $record->investigation_started_by !== null
                            )
                        )

                        /*
                         * Timeline hanya bisa diedit ketika investigasi masih berjalan.
                         * Saat laporan selesai, timeline menjadi read-only.
                         */
                        ->disabled(
                            fn($record) =>
                            $record->status !== LaporanInsiden::STATUS_INVESTIGASI
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
                        ->hidden(
                            fn($record) =>
                            !(
                                Auth::user()?->can('Investigasi:LaporanInsiden')
                                && in_array($record->status, [
                                    LaporanInsiden::STATUS_INVESTIGASI,
                                    LaporanInsiden::STATUS_SELESAI,
                                ])
                                && $record->investigation_started_by !== null
                            )
                        )

                        /*
                         * Analisa masalah hanya dapat diubah selama status masih Investigasi.
                         * Setelah laporan Selesai, data analisa dikunci.
                         */
                        ->disabled(
                            fn($record) =>
                            $record->status !== LaporanInsiden::STATUS_INVESTIGASI
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