<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Section;

class ProblemAnalysisSectionOptimize
{
    public static function make(): Section
    {
        return Section::make('🧠 Analisa Masalah (5 WHY)')
            ->description('Analisis akar masalah berdasarkan metode 5 WHY dengan custom interface yang user-friendly')
            ->schema([
                View::make('filament.components.problem-analysis-livewire-wrapper'),
            ])
            ->collapsible();
    }
}
