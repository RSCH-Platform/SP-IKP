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

class LaporanInsidenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(
                Wizard::make([
                    Step::make('Review Laporan Insiden')
                        ->disabled(fn($record) => $record->status !== LaporanInsiden::STATUS_DRAFT)
                        ->schema([
                            LaporanInsidenFormSchema::sectionPelapor(),
                            LaporanInsidenFormSchema::sectionInsiden(),
                            LaporanInsidenFormSchema::sectionPasien(),
                            LaporanInsidenFormSchema::sectionKronologi(),
                            LaporanInsidenFormSchema::sectionKategoriDampak()->hidden(fn($record) => !($record->status !== LaporanInsiden::STATUS_DRAFT)),
                            LaporanInsidenFormSchema::sectionTindakan(),
                            LaporanInsidenFormSchema::sectionCatatanTambahan()->hidden(fn($record) => !($record->status !== LaporanInsiden::STATUS_DRAFT)),
                        ]),
                    // Step::make('Grading Resiko & Catatan Tambahan') (Laporan Status: Dilaporkan)
                    Step::make('Grading Resiko & Catatan Tambahan')
                        ->hidden(fn($record) => !Auth::user()->can('Verifikasi:LaporanInsiden') || $record->status !== LaporanInsiden::STATUS_DILAPORKAN)
                        ->disabled(fn($record) => ($record->status !== LaporanInsiden::STATUS_DILAPORKAN))
                        ->schema([
                            LaporanInsidenFormSchema::sectionGradingResiko(),
                            LaporanInsidenFormSchema::sectionCatatanTambahan(),
                        ]),

                    // Step::make('Investigasi & Pengumpulan Data') (Laporan Status: Investigasi)
                    Step::make('Pengumpulan Data')
                        ->hidden(fn($record) => !Auth::user()->can('Investigasi:LaporanInsiden') || $record->status !== LaporanInsiden::STATUS_INVESTIGASI)
                        ->disabled(fn($record) => ($record->status !== LaporanInsiden::STATUS_INVESTIGASI))
                        ->schema([
                            LaporanInsidenFormSchema::getFieldDataCollection(),
                        ]),

                    Step::make('Tabular Timeline')
                        ->hidden(fn($record) => !Auth::user()->can('Investigasi:LaporanInsiden') || $record->status !== LaporanInsiden::STATUS_INVESTIGASI)
                        ->disabled(fn($record) => ($record->status !== LaporanInsiden::STATUS_INVESTIGASI))
                        ->schema([
                            LaporanInsidenFormSchema::getFieldTabularTimeline(),
                        ]),

                    Step::make('Analisa Masalah')
                        ->hidden(fn($record) => !Auth::user()->can('Investigasi:LaporanInsiden') || $record->status !== LaporanInsiden::STATUS_INVESTIGASI)
                        ->disabled(fn($record) => ($record->status !== LaporanInsiden::STATUS_INVESTIGASI))
                        ->schema([
                            LaporanInsidenFormSchema::getFieldProblemAnalysis(),
                        ]),
                ])
            )->columns(1);
    }
}
