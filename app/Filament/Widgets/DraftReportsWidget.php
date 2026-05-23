<?php

namespace App\Filament\Widgets;

use App\Models\LaporanInsiden;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DraftReportsWidget extends BaseDraftReportsWidget
{
    protected static ?string $heading = 'Laporan Belum Investigasi';

    protected static ?int $sort = 2;

    public static ?string $modeWidget = 'report';

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return $this->scopedQuery()
            ->whereNull('investigation_started_at')
            ->whereNull('investigation_started_by')
            ->latest('created_at');
    }

    public function table(Table $table): Table
    {
        return $table
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
                ->query(fn (Builder $query, array $data): Builder => $this->applyFilters($query, $data))
                ->columnSpanFull(),
        ];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Tidak ada laporan belum investigasi';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Semua laporan yang tampil sudah memiliki data mulai investigasi atau tidak ada data yang sesuai filter.';
    }

    protected function getFilterSchema(): array
    {
        return [
            Section::make('Pencarian Data')
                ->description('Filter khusus laporan yang belum mulai investigasi')
                ->compact()
                ->schema([
                    Grid::make(3)
                        ->schema($this->getPrimaryFilterFields()),

                    Section::make('Rentang Tanggal')
                        ->compact()
                        ->schema([
                            Grid::make(2)
                                ->schema($this->getDateFilterFields()),
                        ]),

                    Grid::make(3)
                        ->schema($this->getQuickFilterFields()),
                ]),
        ];
    }

    protected function getPrimaryFilterFields(): array
    {
        return [
            ToggleButtons::make('status')
                ->label('Status Laporan')
                ->inline()
                ->columnSpanFull()
                ->options($this->getStatusOptions())
                ->colors($this->getStatusFilterColors()),

            Select::make('jenis_insiden')
                ->label('Jenis Insiden')
                ->multiple()
                ->searchable()
                ->preload()
                ->native(false)
                ->placeholder('Pilih jenis insiden')
                ->options($this->getIncidentTypeOptions()),

            Select::make('unit_kerja_id')
                ->label('Unit Kerja')
                ->multiple()
                ->searchable()
                ->preload()
                ->native(false)
                ->placeholder('Semua unit kerja')
                ->options(fn (): array => $this->getUnitKerjaOptions()),

            ToggleButtons::make('kelengkapan')
                ->label('Kelengkapan')
                ->inline()
                ->grouped()
                ->options($this->getCompletenessOptions())
                ->colors($this->getCompletenessColors()),
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

    protected function getQuickFilterFields(): array
    {
        return [
            Checkbox::make('hanya_belum_nomor')
                ->label('Belum Memiliki Nomor Laporan'),

            Checkbox::make('hanya_timeline_kosong')
                ->label('Timeline Belum Ada'),

            Checkbox::make('hanya_hari_ini')
                ->label('Laporan Hari Ini'),
        ];
    }

    protected function applyFilters(Builder $query, array $data): Builder
    {
        return $query
            ->when(
                $data['status'] ?? null,
                fn (Builder $query, string $value): Builder => $query->where('status', $value)
            )
            ->when(
                $data['jenis_insiden'] ?? null,
                fn (Builder $query, array $values): Builder => $query->whereIn('jenis_insiden', $values)
            )
            ->when(
                $data['unit_kerja_id'] ?? null,
                fn (Builder $query, array $values): Builder => $query->whereIn('unit_kerja_id', $values)
            )
            ->when(
                $data['dari'] ?? null,
                fn (Builder $query, mixed $date): Builder => $query->whereDate('tanggal_lapor', '>=', $date)
            )
            ->when(
                $data['sampai'] ?? null,
                fn (Builder $query, mixed $date): Builder => $query->whereDate('tanggal_lapor', '<=', $date)
            )
            ->when(
                $data['kelengkapan'] ?? null,
                fn (Builder $query, string $value): Builder => $this->applyCompletenessFilter($query, $value)
            )
            ->when(
                $data['hanya_belum_nomor'] ?? false,
                fn (Builder $query): Builder => $query->whereNull('nomor_laporan')
            )
            ->when(
                $data['hanya_timeline_kosong'] ?? false,
                fn (Builder $query): Builder => $query->whereDoesntHave('timelineEvents.entries')
            )
            ->when(
                $data['hanya_hari_ini'] ?? false,
                fn (Builder $query): Builder => $query->whereDate('tanggal_lapor', today())
            );
    }

    protected function applyCompletenessFilter(Builder $query, string $value): Builder
    {
        return match ($value) {
            'lengkap' => $query
                ->whereNotNull('nomor_laporan')
                ->whereHas('timelineEvents.entries'),

            'belum_lengkap' => $query
                ->where(function (Builder $query): void {
                    $query
                        ->whereNull('nomor_laporan')
                        ->orWhereDoesntHave('timelineEvents.entries');
                }),

            'timeline_missing' => $query
                ->whereDoesntHave('timelineEvents.entries'),

            default => $query,
        };
    }
}
