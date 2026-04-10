<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use App\Models\TimelineCategory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;

class TabularTimelineSection
{
    public static function make(): Section
    {
        return Section::make('📊 Tabular Timeline')
            ->description('Timeline kejadian dalam format kronologi investigasi')
            ->schema([
                Repeater::make('timelineEvents')
                    ->relationship('timelineEvents')
                    ->label('Timeline Kejadian')
                    ->minItems(1)
                    ->required()
                    ->schema([
                        DateTimePicker::make('event_datetime')
                            ->label('Tanggal & Waktu Kejadian')
                            ->required()
                            ->seconds(false),

                        Repeater::make('entries')
                            ->relationship('entries')
                            ->label('Entri Kategori')
                            ->minItems(1)
                            ->schema([
                                Hidden::make('id'),

                                Hidden::make('category_id'),

                                Hidden::make('category_name')
                                    ->dehydrated(false),

                                Textarea::make('description')
                                    ->label(fn($get) => $get('category_name'))
                                    ->placeholder(fn($get) => "Tuliskan " . strtolower($get('category_name')) . " di sini...")
                                    ->helperText('Isi dengan jelas dan singkat.')
                                    ->rows(6),
                            ])
                            ->default(function () {
                                return TimelineCategory::orderBy('id')
                                    ->get()
                                    ->map(fn($category) => [
                                        'category_id' => $category->id,
                                        'category_name' => $category->name,
                                        'description' => null,
                                    ])
                                    ->toArray();
                            })
                            ->afterStateHydrated(function ($state, $set, $component) {
                                $categories = TimelineCategory::orderBy('id')->get();

                                $existing = collect($state ?? []);

                                $merged = $categories->map(function ($category) use ($existing) {
                                    $found = $existing->firstWhere('category_id', $category->id);

                                    if ($found) {
                                        $foundArray = is_array($found) ? $found : (array) $found;

                                        return array_merge($foundArray, [
                                            'category_name' => $category->name,
                                        ]);
                                    }

                                    return [
                                        'category_id' => $category->id,
                                        'category_name' => $category->name,
                                        'description' => null,
                                    ];
                                });

                                $set($component, $merged->toArray());
                            })
                            ->itemLabel(function (array $state): ?string {
                                $categoryId = $state['category_id'] ?? null;
                                $description = $state['description'] ?? '-';

                                if (! $categoryId) {
                                    return null;
                                }

                                $categoryName = TimelineCategory::find($categoryId)?->name;

                                if ($categoryName && $description) {
                                    return "{$categoryName}: " . Str::limit($description, 100);
                                }

                                return $categoryName;
                            })
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->collapsible()
                            ->collapsed(),
                    ])
                    ->addActionLabel('Tambah Timeline Kejadian')
                    ->reorderable()
                    ->collapsible()
                    ->collapsed()
                    ->itemLabel(function (array $state): ?string {
                        $datetime = $state['event_datetime'] ?? null;

                        if ($datetime) {
                            return 'Kejadian pada ' . date('d F Y, H:i', strtotime($datetime));
                        }

                        return null;
                    }),
            ])
            ->collapsed()
            ->collapsible();
    }
}
