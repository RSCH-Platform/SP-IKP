<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas;

use App\Models\LaporanInsiden;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LaporanInsidenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(
                Wizard::make([
                    Step::make('Review Laporan Insiden')
                        ->key('review-laporan-insiden')
                        ->disabled(fn($record) => $record->status !== LaporanInsiden::STATUS_DRAFT && ! Auth::user()?->can('ForceEdit:LaporanInsiden'))
                        ->schema([
                            LaporanInsidenFormSchema::sectionPelapor()->disabled(fn($record) => !Auth::user()?->can('ForceEdit:LaporanInsiden') || Auth::id() !== $record->pelapor_id),
                            LaporanInsidenFormSchema::sectionPasien(),
                            LaporanInsidenFormSchema::sectionInsiden(true)->visible(fn($record) => !in_array($record->status, [LaporanInsiden::STATUS_DRAFT, LaporanInsiden::STATUS_DILAPORKAN])),
                            LaporanInsidenFormSchema::sectionInsiden(false)->visible(fn($record) => in_array($record->status, [LaporanInsiden::STATUS_DRAFT, LaporanInsiden::STATUS_DILAPORKAN])),
                            LaporanInsidenFormSchema::sectionKronologi(),
                            LaporanInsidenFormSchema::sectionTindakan(),
                            // LaporanInsidenFormSchema::sectionCatatanTambahan()->hidden(fn($record) => !($record->status !== LaporanInsiden::STATUS_DRAFT)),
                        ]),
                    // Step::make('Grading Resiko & Catatan Tambahan') (Laporan Status: Dilaporkan)
                    Step::make('Grading Resiko & Catatan Tambahan')
                        ->key('grading-resiko-catatan-tambahan')
                        ->hidden(fn($record) => !in_array($record->status, [LaporanInsiden::STATUS_DILAPORKAN, LaporanInsiden::STATUS_REVISI_UNIT]))
                        ->disabled(fn($record) => ($record->status !== LaporanInsiden::STATUS_DILAPORKAN))
                        ->schema([
                            LaporanInsidenFormSchema::sectionGradingResiko(),
                            LaporanInsidenFormSchema::sectionCatatanTambahan(),
                        ]),

                    // Step::make('Investigasi & Pengumpulan Data') (Laporan Status: Investigasi)
                    Step::make('Pengumpulan Data')
                        ->key('pengumpulan-data')
                        ->hidden(
                            fn($record) =>
                            ! (Auth::user()?->can('Investigasi:LaporanInsiden') &&
                                $record->status === LaporanInsiden::STATUS_INVESTIGASI &&
                                $record->investigation_started_by !== null)
                        )
                        ->disabled(fn($record) => ($record->status !== LaporanInsiden::STATUS_INVESTIGASI))
                        ->schema([
                            LaporanInsidenFormSchema::getFieldDataCollection(),
                        ]),

                    Step::make('Tabular Timeline')
                        ->key('tabular-timeline')
                        ->hidden(
                            fn($record) =>
                            ! (Auth::user()?->can('Investigasi:LaporanInsiden') &&
                                $record->status === LaporanInsiden::STATUS_INVESTIGASI &&
                                $record->investigation_started_by !== null)
                        )
                        ->disabled(fn($record) => ($record->status !== LaporanInsiden::STATUS_INVESTIGASI))
                        ->schema([
                            // NEW IMPROVED GRID DESIGN
                            LaporanInsidenFormSchema::getFieldTimelineGrid(),

                            // // OLD DESIGN (preserved for reference/comparison)
                            // LaporanInsidenFormSchema::getFieldTabularTimeline(),
                        ]),

                    Step::make('Analisa Masalah')
                        ->key('analisa-masalah')
                        ->hidden(
                            fn($record) =>
                            ! (Auth::user()?->can('Investigasi:LaporanInsiden') &&
                                $record->status === LaporanInsiden::STATUS_INVESTIGASI &&
                                $record->investigation_started_by !== null)
                        )
                        ->disabled(fn($record) => ($record->status !== LaporanInsiden::STATUS_INVESTIGASI))
                        ->schema([
                            LaporanInsidenFormSchema::getFieldProblemAnalysisOptimize(),
                        ]),
                ])->persistStepInQueryString()
            )->columns(1);
    }
}
