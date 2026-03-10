<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\LaporanInsidens\LaporanInsidenResource;
use App\Models\LaporanInsiden;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DraftReportsWidget extends BaseWidget implements HasTable
{
    use InteractsWithTable;

    protected static ?string $heading = 'Laporan Draft Berdasarkan Unit Kerja';

    protected static ?int $sort = 2;

    /**
     * @var int | string | array<string, int | null>
     */
    protected int | string | array $columnSpan = 2;

    protected function getTableQuery(): Builder
    {
        $query = LaporanInsiden::where('status', [LaporanInsiden::STATUS_DRAFT, LaporanInsiden::STATUS_REVISI]);

        return $query->latest('created_at');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('nomor_laporan')
                ->label('Nomor Laporan')
                ->sortable()
                ->searchable()
                ->color('info')
                ->weight('medium'),

            Tables\Columns\TextColumn::make('unitKerja.unit_name')
                ->label('Unit Kerja')
                ->sortable()
                ->searchable()
                ->badge()
                ->color('primary'),

            Tables\Columns\TextColumn::make('jenis_insiden')
                ->label('Jenis Insiden')
                ->sortable()
                ->searchable()
                ->limit(40),

            Tables\Columns\TextColumn::make('user.name')
                ->label('Pelapor')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('tanggal_lapor')
                ->label('Tanggal Lapor')
                ->date('d M Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat Pada')
                ->dateTime('d M Y H:i')
                ->sortable(),

            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color('warning'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-m-pencil-square')
                ->button()
                ->color('info')
                ->url(fn(LaporanInsiden $record) => LaporanInsidenResource::getUrl('edit', ['record' => $record]))
                ->openUrlInNewTab(false),

            Action::make('view')
                ->label('Lihat')
                ->icon('heroicon-m-eye')
                ->button()
                ->color('success')
                ->url(fn(LaporanInsiden $record) => LaporanInsidenResource::getUrl('view', ['record' => $record]))
                ->openUrlInNewTab(false),
        ];
    }

    protected function getTableEmptyStateActions(): array
    {
        return [
            Action::make('create')
                ->label('Buat Laporan Baru')
                ->icon('heroicon-m-plus')
                ->button()
                ->color('primary')
                ->url(LaporanInsidenResource::getUrl('create'))
                ->openUrlInNewTab(false),
        ];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Tidak ada laporan draft';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Semua laporan dari unit kerja Anda sudah dilaporkan atau tidak ada laporan baru.';
    }
}
