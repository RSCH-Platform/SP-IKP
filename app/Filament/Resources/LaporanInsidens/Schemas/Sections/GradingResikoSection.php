<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section; // Note: original file used this, although non-standard.
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Services\RiskGradingEngine;
use Illuminate\Support\HtmlString;

class GradingResikoSection
{
    public static function make(): Section
    {
        return Section::make('BAGIAN F: GRADING RESIKO')
            ->description('Penilaian tingkat risiko insiden berdasarkan dampak dan probabilitas yang terjadi.')
            ->icon('heroicon-o-exclamation-triangle')
            ->schema([
                Select::make('severity_score')
                    ->label('Penilaian Dampak (Severity)')
                    ->options([
                        1 => '1 — Tidak signifikan (Tidak ada cidera)',
                        2 => '2 — Minor (Cidera ringan / dapat diatasi dengan pertolongan pertama)',
                        3 => '3 — Moderat (Cedera sedang / gangguan fungsi reversibel / memperpanjang perawatan)',
                        4 => '4 — Mayor (Cedera luas / kehilangan fungsi irreversibel)',
                        5 => '5 — Katastropik (Kematian yang tidak berhubungan dengan perjalanan penyakit)',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($set, $get) => self::calculateRisk($set, $get)),

                Select::make('probability_score')
                    ->label('Penilaian Probabilitas (Probability)')
                    ->options([
                        1 => '1 — Sangat jarang / Rare (> 5 tahun/kali)',
                        2 => '2 — Jarang / Unlikely (> 2–5 tahun/kali)',
                        3 => '3 — Mungkin / Possible (1–2 tahun/kali)',
                        4 => '4 — Sering / Likely (Beberapa kali/tahun)',
                        5 => '5 — Sangat sering / Almost Certain (Tiap minggu/bulan)',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($set, $get) => self::calculateRisk($set, $get)),

                Placeholder::make('hasil_grading')
                    ->label('Hasil Grading')
                    ->content(function ($get) {
                        $severity = $get('severity_score');
                        $probability = $get('probability_score');

                        if (!$severity || !$probability) {
                            return new HtmlString("
                                <div class='p-4 border border-dashed rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-500 text-center italic'>
                                    Silakan lengkapi Penilaian Dampak dan Probabilitas terlebih dahulu.
                                </div>
                            ");
                        }

                        $result = RiskGradingEngine::calculate((int)$severity, (int)$probability);
                        
                        $theme = match($result['risk_band']) {
                            'Merah' => [
                                'bg' => 'bg-red-100 dark:bg-red-950/30',
                                'border' => 'border-red-500 dark:border-red-600',
                                'text' => 'text-red-900 dark:text-red-300',
                                'badge' => 'bg-red-600 text-white shadow-sm shadow-red-500/50',
                                'icon' => '🔴',
                            ],
                            'Kuning' => [
                                'bg' => 'bg-yellow-100 dark:bg-yellow-950/30',
                                'border' => 'border-yellow-500 dark:border-yellow-600',
                                'text' => 'text-yellow-900 dark:text-yellow-300',
                                'badge' => 'bg-yellow-500 text-white shadow-sm shadow-yellow-500/50',
                                'icon' => '🟡',
                            ],
                            'Hijau' => [
                                'bg' => 'bg-green-100 dark:bg-green-950/30',
                                'border' => 'border-green-500 dark:border-green-600',
                                'text' => 'text-green-900 dark:text-green-300',
                                'badge' => 'bg-green-500 text-white shadow-sm shadow-green-500/50',
                                'icon' => '🟢',
                            ],
                            'Biru' => [
                                'bg' => 'bg-blue-100 dark:bg-blue-950/30',
                                'border' => 'border-blue-500 dark:border-blue-600',
                                'text' => 'text-blue-900 dark:text-blue-300',
                                'badge' => 'bg-blue-500 text-white shadow-sm shadow-blue-500/50',
                                'icon' => '🔵',
                            ],
                            default => [
                                'bg' => 'bg-gray-100 dark:bg-gray-800',
                                'border' => 'border-gray-500',
                                'text' => 'text-gray-900 dark:text-gray-300',
                                'badge' => 'bg-gray-500 text-white',
                                'icon' => '⚫',
                            ],
                        };

                        return new HtmlString("
                            <div class='p-5 border-2 rounded-xl {$theme['bg']} {$theme['border']} {$theme['text']} transition-all duration-300 ease-in-out shadow-sm'>
                                <div class='flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4'>
                                    <div class='flex items-center gap-3'>
                                        <div class='text-4xl'>{$theme['icon']}</div>
                                        <div>
                                            <div class='text-sm opacity-80 uppercase tracking-wider font-bold'>Risk Band</div>
                                            <div class='text-2xl font-black'>{$result['risk_band']} ({$result['risk_level']})</div>
                                        </div>
                                    </div>
                                    <div class='text-center bg-white/60 dark:bg-black/20 px-6 py-2 rounded-lg border {$theme['border']}'>
                                        <div class='text-xs uppercase tracking-wider font-bold opacity-80'>Risk Score</div>
                                        <div class='text-3xl font-black'>{$result['risk_score']}</div>
                                    </div>
                                </div>
                                <div class='mt-4 pt-4 border-t {$theme['border']} border-opacity-30'>
                                    <div class='font-bold uppercase tracking-wider text-xs mb-2 opacity-80'>Tindakan yang Diperlukan:</div>
                                    <div class='whitespace-pre-wrap font-medium leading-relaxed bg-white/50 dark:bg-black/30 p-4 rounded-lg border {$theme['border']} border-opacity-20'>{$result['required_action']}</div>
                                </div>
                            </div>
                        ");
                    })
                    ->columnSpanFull(),
                    
                // Hidden fields to store calculated values so we can easily access them in mutateFormDataBeforeSave
                \Filament\Forms\Components\Hidden::make('risk_score'),
                \Filament\Forms\Components\Hidden::make('risk_level'),
                \Filament\Forms\Components\Hidden::make('risk_band'),
                \Filament\Forms\Components\Hidden::make('required_action'),
                \Filament\Forms\Components\Hidden::make('grading_risiko'), // Keep for backward compatibility
            ])
            ->columns(1)
            ->collapsible()
            ->compact()
            ->visibleOn('edit');
    }

    private static function calculateRisk($set, $get): void
    {
        $severity = (int)$get('severity_score');
        $probability = (int)$get('probability_score');

        if ($severity && $probability) {
            $result = RiskGradingEngine::calculate($severity, $probability);
            $set('risk_score', $result['risk_score']);
            $set('risk_level', $result['risk_level']);
            $set('risk_band', $result['risk_band']);
            $set('required_action', $result['required_action']);
            
            // Sync with legacy column
            $set('grading_risiko', $result['risk_band']);
        } else {
            $set('risk_score', null);
            $set('risk_level', null);
            $set('risk_band', null);
            $set('required_action', null);
            $set('grading_risiko', null);
        }
    }
}