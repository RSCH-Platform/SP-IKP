<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use App\Models\TimelineCategory;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;

/**
 * Timeline Grid Section - New improved design with Livewire
 * 
 * Displays timeline events in a reactive grid format:
 * - Grouped by Date (vertical sections)
 * - Time as Rows
 * - Categories as Columns
 * - Full CRUD via Livewire actions
 * 
 * This is the IMPROVED UI alongside the old TabularTimelineSection
 */
class TimelineGridSection
{
    public static function make(): Section
    {
        return Section::make('BAGIAN D: TIMELINE INSIDEN')
            ->description('Timeline kejadian dalam format grid - tanggal terpisah, waktu di baris, kategori di kolom')
            ->icon('heroicon-o-clock')
            ->schema([
                // Livewire Timeline Grid Manager Component
                View::make('filament.components.timeline-grid-livewire-wrapper'),
            ])
            ->collapsed()
            ->collapsible();
    }
}
