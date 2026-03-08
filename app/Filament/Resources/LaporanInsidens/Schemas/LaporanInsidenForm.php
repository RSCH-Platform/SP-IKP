<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas;

use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class LaporanInsidenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(
                Wizard::make([
                    Step::make('Review Laporan Insiden')
                        ->schema([
                            LaporanInsidenFormSchema::sectionPelapor(),
                            LaporanInsidenFormSchema::sectionInsiden(),
                            LaporanInsidenFormSchema::sectionInsiden(),
                            LaporanInsidenFormSchema::sectionKronologi(),
                            LaporanInsidenFormSchema::sectionKategoriDampak(),
                            LaporanInsidenFormSchema::sectionTindakan(),
                        ]),
                    Step::make('Grading Resiko Laporan Insiden')
                        ->schema([
                            LaporanInsidenFormSchema::sectionGradingResiko(),
                        ]),

                    Step::make('Review & Submit')
                        ->schema([
                            LaporanInsidenFormSchema::sectionCatatanTambahan(),
                        ]),
                ])
            )->columns(1);
    }
}
