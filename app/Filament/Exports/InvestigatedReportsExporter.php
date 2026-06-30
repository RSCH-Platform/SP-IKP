<?php

namespace App\Filament\Exports;

use App\Models\LaporanInsiden;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Component;
use Illuminate\Database\Eloquent\Builder;

class InvestigatedReportsExporter extends Exporter
{
    protected static ?string $model = LaporanInsiden::class;

    /**
     * @var array<int, string>|null
     */
    protected static ?array $selectedFields = null;

    /**
     * @return array<string, string>
     */
    public static function selectableFields(): array
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
     * @return array<int, string>
     */
    public static function defaultSelectedFields(): array
    {
        return array_keys(static::selectableFields());
    }

    /**
     * @param array<int, string>|null $fields
     */
    public static function setSelectedFields(?array $fields): void
    {
        if (empty($fields)) {
            static::$selectedFields = static::defaultSelectedFields();

            return;
        }

        $allowed = array_keys(static::selectableFields());
        $selected = array_values(array_intersect($allowed, $fields));

        static::$selectedFields = $selected !== []
            ? $selected
            : static::defaultSelectedFields();
    }

    public static function resetSelectedFields(): void
    {
        static::$selectedFields = null;
    }

    /**
     * @return array<Component>
     */
    public static function getOptionsFormComponents(): array
    {
        return [
            CheckboxList::make('selected_fields')
                ->label('Pilih Data yang Diekspor')
                ->options(static::selectableFields())
                ->default(static::defaultSelectedFields())
                ->minItems(1)
                ->required(),
        ];
    }

    protected static function isFieldSelected(string $field): bool
    {
        return in_array($field, static::$selectedFields ?? static::defaultSelectedFields(), true);
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('tanggal_insiden')
                ->label('Tanggal Insiden')
                ->enabledByDefault(fn (): bool => static::isFieldSelected('tanggal_insiden'))
                ->formatStateUsing(fn (mixed $state): string => $state?->format('d M Y') ?? '-'),

            ExportColumn::make('deskripsi_kategori_insiden')
                ->label('Judul Insiden')
                ->enabledByDefault(fn (): bool => static::isFieldSelected('deskripsi_kategori_insiden'))
                ->formatStateUsing(fn (mixed $state, LaporanInsiden $record): string => filled($record->deskripsi_kategori_insiden)
                    ? (string) $record->deskripsi_kategori_insiden
                    : '-'),

            ExportColumn::make('jenis_insiden')
                ->label('Jenis Insiden')
                ->enabledByDefault(fn (): bool => static::isFieldSelected('jenis_insiden'))
                ->formatStateUsing(fn (mixed $state): string => filled($state) ? (string) $state : '-'),

            ExportColumn::make('unit_kerja')
                ->label('Unit Kerja')
                ->enabledByDefault(fn (): bool => static::isFieldSelected('unit_kerja'))
                ->formatStateUsing(fn (mixed $state, LaporanInsiden $record): string => $record->unit_kerja
                    ?? $record->unitKerja?->unit_name
                    ?? '-'),

            ExportColumn::make('akar_masalah')
                ->label('Akar Masalah')
                ->enabledByDefault(fn (): bool => static::isFieldSelected('akar_masalah'))
                ->formatStateUsing(fn (mixed $state, LaporanInsiden $record): string => self::concatenateProblemDescriptions($record)),

            ExportColumn::make('rekomendasi')
                ->label('Rekomendasi')
                ->enabledByDefault(fn (): bool => static::isFieldSelected('rekomendasi'))
                ->formatStateUsing(fn (mixed $state, LaporanInsiden $record): string => self::concatenateRecommendations($record)),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'File Excel data investigasi sudah selesai diproses dan siap diunduh.';
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with([
            'unitKerja',
            'problems.recommendations',
        ]);
    }

    protected static function concatenateProblemDescriptions(LaporanInsiden $record): string
    {
        $descriptions = $record->problems
            ->pluck('problem_description')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $descriptions === []
            ? '-'
            : implode("\n", $descriptions);
    }

    protected static function concatenateRecommendations(LaporanInsiden $record): string
    {
        $recommendations = $record->problems
            ->flatMap(fn ($problem) => $problem->recommendations->pluck('recommendation_text'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $recommendations === []
            ? '-'
            : implode("\n", $recommendations);
    }
}