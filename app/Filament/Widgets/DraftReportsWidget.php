<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\PelaporanInsiden;
use App\Models\LaporanInsiden;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DraftReportsWidget extends BaseWidget implements HasTable
{
    use InteractsWithTable;

    protected static function shouldRender(): bool
    {
        return auth()->check() && auth()->user()->can('viewAny', \App\Models\LaporanInsiden::class);
    }

    protected static ?string $heading = 'Laporan Draft Berdasarkan Unit Kerja';

    protected static ?int $sort = 2;

    /**
     * @var int | string | array<string, int | null>
     */
    protected int | string | array $columnSpan = 2;

    protected function scopedQuery(): Builder
    {
        $query = LaporanInsiden::query()
            ->whereIn('status', [
                LaporanInsiden::STATUS_DRAFT,
                LaporanInsiden::STATUS_REVISI,
            ]);

        if (!auth()->user()?->can('viewAllData', LaporanInsiden::class)) {
            $unitIds = auth()->user()->unitKerja()->pluck('id');
            $query->whereIn('unit_kerja_id', $unitIds);
        }

        return $query;
    }

    protected function getTableQuery(): Builder
    {
        // start with scoped query, then order
        return $this->scopedQuery()->latest('created_at');
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
            /*
                |--------------------------------------------------------------------------
                | Workflow Pelapor
                |--------------------------------------------------------------------------
                */

            Action::make('submit_laporan')
                ->label('Kirim Laporan')
                ->icon('heroicon-o-paper-airplane')
                ->button()
                ->color('warning')
                ->visible(
                    fn($record) =>
                    auth()->user()?->can('Submit:LaporanInsiden') &&
                        in_array($record->status, [
                            LaporanInsiden::STATUS_DRAFT,
                            LaporanInsiden::STATUS_REVISI
                        ])
                )
                ->requiresConfirmation()
                ->modalHeading('Kirim Laporan Insiden?')
                ->modalDescription('Laporan akan dikirim ke kepala unit untuk diverifikasi.')
                ->action(function ($record) {
                    $record->submitLaporan();

                    $kepalaUnits = User::role('kepala_unit')
                        ->whereHas('unitKerja', fn(Builder $query) => $query->where('unit_kerja.id', $record->unit_kerja_id))
                        ->get();

                    Notification::make()
                        ->title('Laporan berhasil dikirim')
                        ->body("Laporan {$record->nomor_laporan} berhasil dikirim ke kepala unit.")
                        ->success()
                        ->send();

                    Notification::make()
                        ->title('Laporan Insiden Baru')
                        ->body("Ada laporan insiden baru dari {$record->nama_pelapor} - {$record->nomor_laporan}")
                        ->warning()
                        ->sendToDatabase($kepalaUnits);
                }),
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
                ->url(PelaporanInsiden::getUrl())
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
