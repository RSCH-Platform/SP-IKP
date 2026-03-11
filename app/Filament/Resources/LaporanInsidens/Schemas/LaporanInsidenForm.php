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
            ->components(
                Wizard::make([
                    Step::make('Review Laporan Insiden')
                        ->disabled(fn($record) => $record->status !== LaporanInsiden::STATUS_DRAFT)
                        ->schema([
                            LaporanInsidenFormSchema::sectionPelapor(),
                            LaporanInsidenFormSchema::sectionInsiden(),
                            LaporanInsidenFormSchema::sectionKronologi(),
                            LaporanInsidenFormSchema::sectionKategoriDampak(),
                            LaporanInsidenFormSchema::sectionTindakan(),
                        ]),
                    Step::make('Grading Resiko & Catatan Tambahan')
                        ->hidden(fn() => !Auth::user()->can('Verifikasi:LaporanInsiden'))
                        ->disabled(fn($record) => ($record->status !== LaporanInsiden::STATUS_DILAPORKAN))
                        ->schema([
                            LaporanInsidenFormSchema::sectionGradingResiko(),
                            LaporanInsidenFormSchema::sectionCatatanTambahan(),
                        ]),

                    // Step::make('Review & Submit')
                    //     ->hidden(fn() => !Auth::user()->can('Verifikasi:LaporanInsiden'))
                    //     ->disabled(fn($record) => !($record->status !== LaporanInsiden::STATUS_DILAPORKAN))
                    //     ->schema([]),
                ])
            )->columns(1);
    }
}
