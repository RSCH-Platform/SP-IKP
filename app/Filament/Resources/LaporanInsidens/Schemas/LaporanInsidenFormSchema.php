<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas;

use App\Filament\Resources\LaporanInsidens\Schemas\Sections\CatatanTambahanSection;
use App\Filament\Resources\LaporanInsidens\Schemas\Sections\DataCollectionSection;
use App\Filament\Resources\LaporanInsidens\Schemas\Sections\GradingResikoSection;
use App\Filament\Resources\LaporanInsidens\Schemas\Sections\InsidenSection;
use App\Filament\Resources\LaporanInsidens\Schemas\Sections\PasienSection;
use App\Filament\Resources\LaporanInsidens\Schemas\Sections\PelaporSection;
use App\Filament\Resources\LaporanInsidens\Schemas\Sections\ProblemAnalysisSection;
use App\Filament\Resources\LaporanInsidens\Schemas\Sections\StatusSection;
use App\Filament\Resources\LaporanInsidens\Schemas\Sections\TabularTimelineSection;
use App\Filament\Resources\LaporanInsidens\Schemas\Sections\TimelineGridSection;
use App\Filament\Resources\LaporanInsidens\Schemas\Sections\TindakanSection;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;

class LaporanInsidenFormSchema
{
    public static function steps(bool $withAdminFields = false): array
    {
        $steps = [
            Step::make('Pelapor')
                ->icon('heroicon-o-user-circle')
                ->schema([
                    static::sectionPelapor(),
                ]),

            Step::make('Insiden')
                ->icon('heroicon-o-exclamation-triangle')
                ->schema([
                    static::sectionInsiden(),
                ]),

            Step::make('Pasien')
                ->icon('heroicon-o-identification')
                ->hidden(fn(Get $get) => $get('insiden_terjadi_pada') !== 'Pasien')
                ->schema([
                    static::sectionPasien(),
                ]),

            Step::make('Kronologi')
                ->icon('heroicon-o-document-text')
                ->schema([
                    static::sectionKronologi(),
                ]),

            Step::make('Tindakan')
                ->icon('heroicon-o-hand-raised')
                ->schema([
                    static::sectionTindakan($withAdminFields),
                ]),
        ];

        if ($withAdminFields) {
            $steps[] = Step::make('Catatan')
                ->icon('heroicon-o-paper-clip')
                ->schema([
                    static::sectionCatatanTambahan(),
                ]);

            $steps[] = Step::make('Status')
                ->icon('heroicon-o-check-circle')
                ->schema([
                    static::sectionStatus(),
                ]);
        }

        return $steps;
    }

    /**
     * Kembalikan semua section form.
     *
     * @param bool $withAdminFields Sertakan field khusus admin (grading, status, catatan)
     */
    public static function sections(bool $withAdminFields = false): array
    {
        $sections = [
            static::sectionPelapor(),
            static::sectionInsiden(),
            static::sectionPasien(),
            static::sectionKronologi(),
            static::sectionTindakan($withAdminFields),
        ];

        if ($withAdminFields) {
            $sections[] = static::sectionCatatanTambahan();
            $sections[] = static::sectionStatus();
        }

        return $sections;
    }

    public static function sectionPelapor(): Section
    {
        return PelaporSection::make();
    }

    public static function sectionInsiden(bool $withGrading = false): Section
    {
        return InsidenSection::make($withGrading);
    }

    public static function sectionPasien(): Section
    {
        return PasienSection::make();
    }

    public static function sectionKronologi(): Section
    {
        return TabularTimelineSection::make();
    }

    public static function sectionTindakan(bool $withAnalysis = false): Section
    {
        return TindakanSection::make($withAnalysis);
    }

    public static function sectionCatatanTambahan(): Section
    {
        return CatatanTambahanSection::make();
    }

    public static function sectionGradingResiko()
    {
        return GradingResikoSection::make();
    }

    public static function sectionStatus(): Section
    {
        return StatusSection::make();
    }

    public static function getFieldDataCollection(): Section
    {
        return DataCollectionSection::make();
    }

    public static function getFieldTabularTimeline(): Section
    {
        return TabularTimelineSection::make();
    }

    public static function getFieldProblemAnalysis(): Section
    {
        return ProblemAnalysisSection::make();
    }

    public static function getFieldTimelineGrid(): Section
    {
        return TimelineGridSection::make();
    }
}
