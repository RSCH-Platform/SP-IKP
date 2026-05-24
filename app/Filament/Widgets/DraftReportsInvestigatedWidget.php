<?php

namespace App\Filament\Widgets;

use App\Models\LaporanInsiden;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DraftReportsInvestigatedWidget extends BaseDraftReportsWidget
{
    protected static ?string $heading = 'Laporan Sudah Mulai Investigasi';

    protected static ?int $sort = 3;

    /**
     * @var int | string | array<string, int | null>
     */
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user !== null && (
            $user->can('Submit:LaporanInsiden')
            || $user->can('ForceEdit:LaporanInsiden')
            || $user->can('ViewAllData:LaporanInsiden')
        );
    }

    protected function getTableQuery(): Builder
    {
        return $this->scopedQuery()
            ->whereNotNull('investigation_started_at')
            ->whereNotNull('investigation_started_by')
            ->latest('created_at');
    }

    public function table(Table $table): Table
    {
        return $table
            ->description('Laporan yang sudah mulai investigasi, namun belum selesai. Laporan dengan status ini biasanya masih dalam proses pengumpulan data dan analisis awal oleh tim investigasi.')
            ->headerActions([
                Action::make('export')
                    ->label('Ekspor Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->schema([
                        CheckboxList::make('selected_fields')
                            ->label('Pilih Data yang Diekspor')
                            ->options($this->exportFieldOptions())
                            ->default(array_keys($this->exportFieldOptions()))
                            ->columns(2)
                            ->minItems(1)
                            ->required(),
                    ])
                    ->modalHeading('Ekspor Data Investigasi')
                    ->modalDescription('File akan langsung diunduh tanpa disimpan ke sistem.')
                    ->modalSubmitActionLabel('Unduh Excel')
                    ->action(function (array $data): StreamedResponse {
                        $selectedFields = $this->normalizeSelectedExportFields($data['selected_fields'] ?? []);

                        return response()->streamDownload(function () use ($selectedFields): void {
                            $writer = new Writer();
                            $writer->openToFile('php://output');

                            $labels = $this->exportFieldOptions();

                            $writer->addRow(Row::fromValues(array_map(
                                fn(string $field): string => $labels[$field],
                                $selectedFields,
                            )));

                            $query = $this->getTableQueryForExport();

                            if (in_array('unit_kerja', $selectedFields, true)) {
                                $query->with('unitKerjas');
                            }

                            if (
                                in_array('akar_masalah', $selectedFields, true)
                                || in_array('rekomendasi', $selectedFields, true)
                            ) {
                                $query->with(['problems.whys', 'problems.recommendations']);
                            }

                            $query->chunk(200, function (Collection $records) use ($writer, $selectedFields): void {
                                foreach ($records as $record) {
                                    foreach ($this->mapExportRows($record, $selectedFields) as $rowValues) {
                                        $writer->addRow(Row::fromValues($rowValues));
                                    }
                                }
                            });

                            $writer->close();
                        }, 'laporan-investigasi-' . now()->format('Ymd-His') . '.xlsx');
                    }),
            ])
            ->filtersLayout(FiltersLayout::Dropdown)
            ->filtersFormWidth('6xl')
            ->filtersFormColumns(3)
            ->deferFilters(false);
    }

    protected function getTableFilters(): array
    {
        return [
            Filter::make('filters')
                ->schema($this->getFilterSchema())
                ->query(fn(Builder $query, array $data): Builder => $this->applyFilters($query, $data))
                ->columnSpanFull(),
        ];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Tidak ada laporan yang sudah investigasi';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Belum ada laporan dengan data mulai investigasi yang sudah terisi.';
    }

    protected function getFilterSchema(): array
    {
        return [
            Section::make('Pencarian Data')
                ->description('Filter khusus laporan yang sudah mulai investigasi')
                ->compact()
                ->schema([
                    Grid::make(3)
                        ->schema($this->getMainFilterFields()),

                    Section::make('Rentang Tanggal')
                        ->compact()
                        ->schema([
                            Grid::make(2)
                                ->schema($this->getDateFilterFields()),
                        ]),
                ]),
        ];
    }

    protected function getMainFilterFields(): array
    {
        return [
            Select::make('unit_kerja_id')
                ->label('Unit Kerja')
                ->multiple()
                ->searchable()
                ->preload()
                ->native(false)
                ->placeholder('Semua unit kerja')
                ->options(fn(): array => $this->getUnitKerjaOptions()),

            Select::make('jenis_insiden')
                ->label('Jenis Insiden')
                ->multiple()
                ->searchable()
                ->preload()
                ->native(false)
                ->placeholder('Pilih jenis insiden')
                ->options($this->getIncidentTypeOptions()),

            Select::make('status')
                ->label('Status Laporan')
                ->multiple()
                ->searchable()
                ->preload()
                ->native(false)
                ->placeholder('Semua status')
                ->options($this->getStatusOptions()),
        ];
    }

    protected function getDateFilterFields(): array
    {
        return [
            DatePicker::make('dari')
                ->label('Dari Tanggal')
                ->native(false)
                ->displayFormat('d M Y')
                ->closeOnDateSelection(),

            DatePicker::make('sampai')
                ->label('Sampai Tanggal')
                ->native(false)
                ->displayFormat('d M Y')
                ->closeOnDateSelection(),
        ];
    }

    protected function applyFilters(Builder $query, array $data): Builder
    {
        return $query
            ->when(
                $data['status'] ?? null,
                fn(Builder $query, array|string $value): Builder => $query->whereIn('status', (array) $value)
            )
            ->when(
                $data['jenis_insiden'] ?? null,
                fn(Builder $query, array $values): Builder => $query->whereIn('jenis_insiden', $values)
            )
            ->when(
                $data['unit_kerja_id'] ?? null,
                fn(Builder $query, array $values): Builder => $query->whereIn('unit_kerja_id', $values)
            )
            ->when(
                $data['dari'] ?? null,
                fn(Builder $query, mixed $date): Builder => $query->whereDate('tanggal_lapor', '>=', $date)
            )
            ->when(
                $data['sampai'] ?? null,
                fn(Builder $query, mixed $date): Builder => $query->whereDate('tanggal_lapor', '<=', $date)
            );
    }

    /**
     * @return array<string, string>
     */
    protected function exportFieldOptions(): array
    {
        return [
            'tanggal_insiden' => 'Tanggal Insiden',
            'deskripsi_kategori_insiden' => 'Judul Insiden',
            'jenis_insiden' => 'Jenis Insiden',
            'unit_kerja' => 'Unit Kerja',
            'akar_masalah' => 'Akar Masalah',
            'rekomendasi' => 'Rekomendasi',
        ];
    }

    /**
     * @param array<int, string> $selectedFields
     * @return array<int, string>
     */
    protected function normalizeSelectedExportFields(array $selectedFields): array
    {
        $allowed = array_keys($this->exportFieldOptions());
        $filtered = array_values(array_intersect($allowed, $selectedFields));

        return $filtered !== [] ? $filtered : $allowed;
    }

    /**
     * @param array<int, string> $selectedFields
     * @return array<int, array<int, string>>
     */
    protected function mapExportRows(LaporanInsiden $record, array $selectedFields): array
    {
        if (
            !in_array('akar_masalah', $selectedFields, true)
            && !in_array('rekomendasi', $selectedFields, true)
        ) {
            return [$this->formatRowBySelectedFields($this->buildBaseRow($record), $selectedFields)];
        }

        $problemRows = $this->buildProblemRows($record);

        if ($problemRows === []) {
            $problemRows = [
                [
                    'akar_masalah' => '-',
                    'rekomendasi' => '-',
                ]
            ];
        }

        $rows = [];

        foreach ($problemRows as $problemRow) {
            $rows[] = $this->formatRowBySelectedFields(
                array_merge($this->buildBaseRow($record), $problemRow),
                $selectedFields,
            );
        }

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    protected function buildBaseRow(LaporanInsiden $record): array
    {
        $tanggalInsiden = $record->tanggal_insiden;

        if ($tanggalInsiden instanceof \DateTimeInterface) {
            $formattedTanggalInsiden = $tanggalInsiden->format('d M Y');
        } elseif (filled($tanggalInsiden)) {
            $formattedTanggalInsiden = (string) $tanggalInsiden;
        } else {
            $formattedTanggalInsiden = '-';
        }

        return [
            'tanggal_insiden' => $formattedTanggalInsiden,
            'deskripsi_kategori_insiden' => $record->deskripsi_kategori_insiden ?: '-',
            'jenis_insiden' => $record->jenis_insiden ?: '-',
            'unit_kerja' => $record->unit_kerja ?? $record->unitKerjas?->unit_name ?? '-',
            'akar_masalah' => '-',
            'rekomendasi' => '-',
        ];
    }

    /**
     * @param array<string, string> $rowData
     * @param array<int, string> $selectedFields
     * @return array<int, string>
     */
    protected function formatRowBySelectedFields(array $rowData, array $selectedFields): array
    {
        $values = [];

        foreach ($selectedFields as $field) {
            $values[] = $rowData[$field] ?? '-';
        }

        return $values;
    }

    /**
     * @return array<int, array{akar_masalah: string, rekomendasi: string}>
     */
    protected function buildProblemRows(LaporanInsiden $record): array
    {
        $rows = [];

        foreach ($record->problems as $problem) {
            $latestWhyLevel = $problem->whys->max('why_level');

            $akarMasalahItems = $problem->whys
                ->when(
                    filled($latestWhyLevel),
                    fn(Collection $whys): Collection => $whys->where('why_level', $latestWhyLevel),
                )
                ->pluck('problem_statement')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $recommendationItems = $problem->recommendations
                ->pluck('recommendation_text')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $akarMasalahItems = $akarMasalahItems !== [] ? $akarMasalahItems : ['-'];
            $recommendationItems = $recommendationItems !== [] ? $recommendationItems : ['-'];

            $maxRows = max(count($akarMasalahItems), count($recommendationItems));

            for ($index = 0; $index < $maxRows; $index++) {
                $rows[] = [
                    'akar_masalah' => $akarMasalahItems[$index] ?? '-',
                    'rekomendasi' => $recommendationItems[$index] ?? '-',
                ];
            }
        }

        return $rows;
    }
}
