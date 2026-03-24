<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use App\Models\ProblemContributorCategory;
use App\Models\ProblemContributorComponent;
use App\Models\ProblemContributorDescription;
use App\Models\ProblemContributorSubComponent;
use App\Models\TimelineCategory;
use App\Models\TimelineEntry;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;

class ProblemAnalysisSection
{
    public static function make(): Section
    {
        return Section::make('🧠 Analisa Masalah (5 WHY)')
            ->description('Analisis akar masalah berdasarkan metode 5 WHY')
            ->schema([
                Repeater::make('problems')
                    ->relationship('problems')
                    ->label('Masalah (CMP / SDP)')
                    ->schema([
                        Hidden::make('problem_type')
                            ->label('Jenis Masalah')
                            ->dehydrated()
                            ->required(),

                        Textarea::make('problem_description')
                            ->label('Detail Masalah')
                            ->readOnly()
                            ->columnSpanFull()
                            ->rows(3)
                            ->required(),

                        Repeater::make('whys')
                            ->relationship('whys')
                            ->label('Analisa 5 WHY')
                            ->schema([
                                Hidden::make('why_level')
                                    ->default(fn(callable $get) => count($get('../../whys') ?? []) + 1)
                                    ->dehydrated(false),

                                Fieldset::make()
                                    ->label(fn($get) => 'WHY ke-' . ($get('why_level') ?? '?'))
                                    ->schema([
                                        Textarea::make('problem_statement')
                                            ->label('Masalah')
                                            ->columnSpanFull()
                                            ->placeholder(function ($get) {
                                                $level = $get('why_level') ?? 1;
                                                return 'Masukkan penjelasan WHY ke-' . $level;
                                            })
                                            ->rows(2)
                                            ->required(),
                                    ]),
                            ])
                            ->addActionLabel('➕ Tambah WHY')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(function (array $state, callable $get): ?string {
                                $allWhys = $get('whys') ?? [];

                                // cari current why berdasarkan ID
                                $current = collect($allWhys)->firstWhere('id', $state['id'] ?? null);

                                $level = $current['why_level'] ?? null;

                                // cari max level (bukan count, karena bisa gap)
                                $maxLevel = collect($allWhys)->max('why_level');

                                $isLast = $level !== null && $level === $maxLevel;

                                $label = $isLast
                                    ? 'Akar Masalah'
                                    : ($level ? "WHY {$level}" : 'WHY');

                                if (! empty($state['problem_statement'])) {
                                    return $label . ': ' . Str::limit($state['problem_statement'], 40);
                                }

                                return $label;
                            }),

                        Repeater::make('contributors')
                            ->relationship('contributors')
                            ->label('Faktor Kontributor')
                            ->schema([
                                Select::make('category_id')
                                    ->label('Kategori')
                                    ->searchable()
                                    ->preload()
                                    ->relationship('category', 'name', fn($query) => $query->orderBy('name'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn(callable $set) => $set('component_id', null)),

                                Select::make('component_id')
                                    ->label('Komponen')
                                    ->searchable()
                                    ->preload()
                                    ->options(function ($get) {
                                        $categoryId = $get('category_id');

                                        if (! $categoryId) {
                                            return [];
                                        }

                                        return ProblemContributorComponent::where('category_id', $categoryId)
                                            ->orderBy('name')
                                            ->pluck('name', 'id');
                                    })
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn(callable $set) => $set('sub_component_id', null)),

                                Select::make('sub_component_id')
                                    ->label('Sub Komponen')
                                    ->searchable()
                                    ->preload()
                                    ->options(function ($get) {
                                        $componentId = $get('component_id');

                                        if (! $componentId) {
                                            return [];
                                        }

                                        return ProblemContributorSubComponent::where('component_id', $componentId)
                                            ->orderBy('name')
                                            ->pluck('name', 'id');
                                    })
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        $subComponentId = $get('sub_component_id');

                                        if (! $subComponentId) {
                                            $set('description', null);
                                            return;
                                        }

                                        $descriptions = ProblemContributorDescription::where('sub_component_id', $subComponentId)
                                            ->orderBy('id')
                                            ->pluck('description')
                                            ->toArray();

                                        if (! empty($descriptions)) {
                                            $autoFilled = implode("\n", array_map(fn($desc) => "• {$desc}", $descriptions));
                                            $set('description', $autoFilled);
                                        } else {
                                            $set('description', null);
                                        }
                                    }),

                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(10)
                                    ->hint(function ($get) {
                                        $subComponentId = $get('sub_component_id');

                                        if (! $subComponentId) {
                                            return null;
                                        }

                                        $count = ProblemContributorDescription::where('sub_component_id', $subComponentId)->count();
                                        return $count > 0 ? "💡 {$count} deskripsi tersedia (auto-filled)" : null;
                                    }),
                            ])
                            ->addActionLabel('Tambah Faktor')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                $categoryId = $state['category_id'] ?? null;
                                $componentId = $state['component_id'] ?? null;
                                $subId = $state['sub_component_id'] ?? null;

                                $category = $categoryId ? ProblemContributorCategory::find($categoryId)?->name : null;
                                $component = $componentId ? ProblemContributorComponent::find($componentId)?->name : null;
                                $sub = $subId ? ProblemContributorSubComponent::find($subId)?->name : null;

                                if ($category && $component && $sub) {
                                    return "{$category} > {$component} > {$sub}";
                                }

                                if ($category && $component) {
                                    return "{$category} > {$component}";
                                }

                                return $category;
                            }),

                        Repeater::make('recommendations')
                            ->relationship('recommendations')
                            ->label('Rekomendasi')
                            ->schema([
                                Textarea::make('recommendation_text')
                                    ->label('Rekomendasi')
                                    ->rows(2)
                                    ->required(),

                                Select::make('priority')
                                    ->label('Prioritas')
                                    ->options([
                                        'low' => 'Low',
                                        'medium' => 'Medium',
                                        'high' => 'High',
                                    ]),
                            ])
                            ->addActionLabel('Tambah Rekomendasi')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                $text = $state['recommendation_text'] ?? null;

                                if ($text) {
                                    return Str::limit($text, 50);
                                }

                                return null;
                            }),

                        Repeater::make('actions')
                            ->relationship('actions')
                            ->label('Tindakan')
                            ->schema([
                                Textarea::make('action_text')
                                    ->label('Tindakan')
                                    ->rows(2)
                                    ->required(),

                                TextInput::make('responsible_person')
                                    ->label('Penanggung Jawab'),

                                DatePicker::make('deadline')
                                    ->label('Deadline'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'ongoing' => 'Ongoing',
                                        'completed' => 'Completed',
                                    ])
                                    ->default('pending'),

                                SpatieMediaLibraryFileUpload::make('evidence_files')
                                    ->label('Upload Bukti (Bisa lebih dari 1 file)')
                                    ->collection('action_evidence')
                                    ->disk('public')
                                    ->multiple()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'application/pdf'])
                                    ->maxSize(5120)
                                    ->previewable(true)
                                    ->helperText('Klik atau drag & drop beberapa file sekaligus. Maks 5MB per file.')
                            ])
                            ->addActionLabel('Tambah Tindakan')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                $action = $state['action_text'] ?? null;

                                if ($action) {
                                    return Str::limit($action, 50);
                                }

                                return null;
                            }),
                    ])
                    ->addable(false)
                    ->deletable(false)
                    ->afterStateHydrated(function ($state, \Filament\Schemas\Components\Utilities\Set $set, $component) {
                        // If the form already has problems, keep them.
                        if (! empty($state)) {
                            return;
                        }

                        $categoryIds = TimelineCategory::whereIn('code', ['cmp', 'sdp'])
                            ->pluck('id')
                            ->toArray();

                        $set(
                            $component,
                            TimelineEntry::with('category')
                                ->whereIn('category_id', $categoryIds)
                                ->orderBy('id')
                                ->get()
                                ->groupBy(fn(TimelineEntry $entry) => strtoupper($entry->category?->code ?? ''))
                                ->map(fn($group) => $group->first())
                                ->map(fn(TimelineEntry $entry) => [
                                    'problem_type' => strtoupper($entry->category?->code ?? ''),
                                    'problem_description' => $entry->description,
                                ])
                                ->values()
                                ->toArray()
                        );
                    })
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(function (array $state): ?string {
                        $type = $state['problem_type'] ?? null;
                        $desc = $state['problem_description'] ?? null;

                        if ($type && $desc) {
                            return "{$type}: " . Str::limit($desc, 100);
                        }

                        return $type;
                    }),
            ])
            ->collapsible();
    }
}
