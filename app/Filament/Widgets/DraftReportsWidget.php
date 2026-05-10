<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\PelaporanInsiden;
use App\Filament\Resources\LaporanInsidens\LaporanInsidenResource;
use App\Models\LaporanInsiden;
use App\Models\UnitKerja;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class DraftReportsWidget extends BaseWidget implements HasTable
{
    use InteractsWithTable, HasWidgetShield;

    protected static ?string $heading = 'List Laporan Terbaru';

    protected static ?int $sort = 2;

    /**
     * @var int | string | array<string, int | null>
     */
    protected int | string | array $columnSpan = 2;

    protected function scopedQuery(): Builder
    {
        $query = LaporanInsiden::query();
        $user = auth()->user();

        if (! $user) {
            return $query;
        }

        // submitter biasa
        if (
            $user->can('Submit:LaporanInsiden') &&
            ! $user->can('ForceEdit:LaporanInsiden') &&
            ! $user->can('ViewAllData:LaporanInsiden')
        ) {
            return $query
                ->whereIn('status', [
                    LaporanInsiden::STATUS_DRAFT,
                    LaporanInsiden::STATUS_REVISI,
                ])
                ->where('user_id', $user->getKey());
        }

        // kepala unit
        if (
            $user->can('ForceEdit:LaporanInsiden') &&
            ! $user->can('ViewAllData:LaporanInsiden')
        ) {
            $unitIds = $user->unitKerjas()->pluck('id');

            return $query->whereIn('unit_kerja_id', $unitIds);
        }

        return $query;
    }

    protected function getTableQuery(): Builder
    {
        // start with scoped query, then order
        return $this->scopedQuery()->latest('created_at');
    }

    public function table(Table $table): Table
    {
        return $table
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3)
            ->deferFilters(false);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('nomor_laporan')
                ->label('Nomor Laporan')
                ->sortable()
                ->searchable()
                ->toggleable()
                ->weight('medium'),

            Tables\Columns\TextColumn::make('deskripsi_kategori_insiden')
                ->label('Deskripsi Insiden')
                ->searchable()
                ->limit(80)
                ->toggleable(),

            Tables\Columns\TextColumn::make('unit_kerja')
                ->label('Unit Kerja')
                ->sortable()
                ->searchable()
                ->badge()
                ->toggleable()
                ->color('primary'),

            Tables\Columns\TextColumn::make('jenis_insiden')
                ->label('Jenis Insiden')
                ->sortable()
                ->searchable()
                ->toggleable()
                ->limit(40),

            Tables\Columns\TextColumn::make('user.name')
                ->label('Pelapor')
                ->sortable()
                ->toggleable()
                ->searchable(),

            Tables\Columns\TextColumn::make('tanggal_lapor')
                ->label('Tanggal Lapor')
                ->toggleable()
                ->date('d M Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat Pada')
                ->dateTime('d M Y H:i')
                ->toggleable()
                ->sortable(),

            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->toggleable()
                ->color('warning'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-m-eye')
                    ->url(fn($record) => LaporanInsidenResource::getUrl('view', ['record' => $record->id]))
                    ->openUrlInNewTab(false)
                    ->visible(fn($record) => auth()->user()?->can('View:LaporanInsiden')),

                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-m-pencil')
                    ->url(fn($record) => LaporanInsidenResource::getUrl('edit', ['record' => $record->id]))
                    ->openUrlInNewTab(false)
                    ->visible(
                        fn($record) =>
                        auth()->user()?->can('Update:LaporanInsiden') &&
                            in_array($record->status, [
                                LaporanInsiden::STATUS_DRAFT,
                                LaporanInsiden::STATUS_REVISI,
                            ], true)
                    ),

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
                                LaporanInsiden::STATUS_REVISI,
                            ], true)
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Laporan Insiden?')
                    ->modalDescription('Laporan akan dikirim ke kepala unit untuk diverifikasi.')
                    ->action(function (LaporanInsiden $record): void {
                        $missingFields = $this->getMissingSubmitFields($record);

                        if ($missingFields !== []) {
                            Notification::make()
                                ->title('Laporan belum bisa dikirim')
                                ->body('Lengkapi field wajib berikut: ' . implode(', ', $missingFields))
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->submitLaporan();

                        $kepalaUnits = User::role('kepala_unit')
                            ->whereHas('unitKerjas', fn(Builder $query) => $query->where('unit_kerja.id', $record->unit_kerja_id))
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

                Action::make('verifikasi_laporan')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->button()
                    ->color('success')
                    ->visible(
                        fn($record) =>
                        auth()->user()?->can('Verifikasi:LaporanInsiden') &&
                            $record->status === LaporanInsiden::STATUS_DILAPORKAN
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Laporan?')
                    ->modalDescription('Laporan akan diteruskan ke tim mutu untuk investigasi.')
                    ->schema([
                        ToggleButtons::make('grading_risiko')
                            ->label('Grading Risiko')
                            ->required()
                            ->options([
                                'Biru' => '🔵 Biru (Tidak signifikan)',
                                'Hijau' => '🟢 Hijau (Minor)',
                                'Kuning' => '🟡 Kuning (Moderat)',
                                'Merah' => '🔴 Merah (Mayor)',
                                'Hitam' => '⚫ Hitam (Katastropik)',
                            ])
                            ->colors([
                                'Biru' => 'info',
                                'Hijau' => 'success',
                                'Kuning' => 'warning',
                                'Merah' => 'danger',
                                'Hitam' => 'gray',
                            ])
                            ->inline()
                            ->helperText('Hanya diisi oleh Validator / Tim IKP')
                            ->default(fn($record) => $record->grading_risiko),
                        Textarea::make('catatan_tambahan')
                            ->label('Catatan Verifikasi')
                            ->rows(3)
                            ->default(fn($record) => $record->catatan_tambahan),
                    ])
                    ->action(function (LaporanInsiden $record, array $data): void {
                        $record->update([
                            'grading_risiko' => $data['grading_risiko'],
                        ]);

                        $record->verifikasiLaporan(auth()->id());

                        $timMutu = User::role(['tim_mutu', 'admin_ikp'])->get();

                        Notification::make()
                            ->title('Laporan berhasil diverifikasi')
                            ->success()
                            ->send();

                        if ($timMutu->isNotEmpty()) {
                            Notification::make()
                                ->title('Laporan Siap Investigasi')
                                ->body("Laporan dari {$record->nama_pelapor} telah diverifikasi dan siap untuk investigasi.")
                                ->info()
                                ->sendToDatabase($timMutu);
                        }
                    }),

                Action::make('mulai_investigasi')
                    ->label('Mulai Investigasi')
                    ->icon('heroicon-o-magnifying-glass')
                    ->button()
                    ->color('info')
                    ->visible(
                        fn($record) =>
                        auth()->user()?->can('Investigasi:LaporanInsiden') &&
                            $record->status === LaporanInsiden::STATUS_DIVERIFIKASI
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Mulai Investigasi?')
                    ->modalDescription('Investigasi akan dimulai setelah data verifikasi lengkap.')
                    ->action(function (LaporanInsiden $record): void {
                        if (blank($record->grading_risiko)) {
                            Notification::make()
                                ->title('Belum bisa investigasi')
                                ->body('Grading risiko wajib diisi saat verifikasi sebelum memulai investigasi.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->mulaiInvestigasi(auth()->id());

                        Notification::make()
                            ->title('Investigasi dimulai')
                            ->body("Laporan {$record->nomor_laporan} sekarang masuk ke tahap investigasi.")
                            ->success()
                            ->send();
                    }),
            ])
                ->button()
                ->label('Aksi')
                ->icon('heroicon-o-ellipsis-vertical'),
        ];
    }

    protected function getMissingSubmitFields(LaporanInsiden $record): array
    {
        $requiredFieldsForSubmit = [
            'nama_pelapor' => 'Nama Pelapor',
            'unit_kerja_id' => 'Unit Kerja',
            'tanggal_lapor' => 'Tanggal Lapor',
            'jenis_insiden' => 'Jenis Insiden',
            'tanggal_insiden' => 'Tanggal Insiden',
            'waktu_insiden' => 'Waktu Insiden',
            'lokasi_insiden' => 'Lokasi Insiden',
            'insiden_terjadi_pada' => 'Insiden Terjadi Pada',
            'kategori_insiden' => 'Kategori Insiden',
            'deskripsi_kategori_insiden' => 'Deskripsi Kategori Insiden',
            'dampak_insiden' => 'Dampak Insiden',
            'tindakan_dilakukan' => 'Tindakan Dilakukan',
        ];

        $missingFields = collect($requiredFieldsForSubmit)
            ->filter(fn($label, $field) => blank(data_get($record, $field)))
            ->values()
            ->all();

        if (! $record->timelineEvents()->whereHas('entries')->exists()) {
            $missingFields[] = 'Kronologi (Timeline)';
        }

        return $missingFields;
    }

    public function getTableFilters(): array
    {
        return [
            Filter::make('filters')
                ->schema([

                    Section::make('Pencarian Data')
                        ->description('Filter laporan insiden')
                        ->compact()
                        ->schema([

                            Grid::make(3)
                                ->schema([

                                    ToggleButtons::make('status')
                                        ->label('Status Laporan')
                                        ->inline()
                                        ->columnSpanFull()
                                        ->options([
                                            'draft' => 'Draft',
                                            'dilaporkan' => 'Dilaporkan',
                                            'revisi_unit' => 'Revisi Unit',
                                            'revisi' => 'Revisi',
                                            'diverifikasi' => 'Diverifikasi',
                                            'investigasi' => 'Investigasi',
                                        ])
                                        ->colors([
                                            'draft' => 'gray',
                                            'dilaporkan' => 'info',
                                            'revisi_unit' => 'warning',
                                            'revisi' => 'danger',
                                            'diverifikasi' => 'success',
                                            'investigasi' => 'primary',
                                        ]),

                                    Select::make('jenis_insiden')
                                        ->label('Jenis Insiden')
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->placeholder('Pilih jenis insiden')
                                        ->options([
                                            'KPC (Kondisi Potensial Cedera)' => 'KPC',
                                            'KNC (Kejadian Nyaris Cedera)' => 'KNC',
                                            'KTC (Kejadian Tidak Cedera)' => 'KTC',
                                            'KTD (Kejadian Tidak Diharapkan)' => 'KTD',
                                            'Sentinel' => 'Sentinel',
                                        ]),

                                    Select::make('unit_kerja_id')
                                        ->label('Unit Kerja')
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->placeholder('Semua unit kerja')
                                        ->options(
                                            UnitKerja::query()
                                                ->orderBy('unit_name')
                                                ->pluck('unit_name', 'id')
                                                ->all()
                                        ),

                                    ToggleButtons::make('kelengkapan')
                                        ->label('Kelengkapan')
                                        ->inline()
                                        ->grouped()
                                        ->options([
                                            'lengkap' => 'Lengkap',
                                            'belum_lengkap' => 'Belum Lengkap',
                                            'timeline_missing' => 'Timeline Kosong',
                                        ])
                                        ->colors([
                                            'lengkap' => 'success',
                                            'belum_lengkap' => 'warning',
                                            'timeline_missing' => 'danger',
                                        ]),

                                ]),

                            Section::make('Rentang Tanggal')
                                ->compact()
                                ->schema([
                                    Grid::make(2)
                                        ->schema([

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
                                        ]),
                                ]),

                            Grid::make(3)
                                ->schema([

                                    Checkbox::make('hanya_belum_nomor')
                                        ->label('Belum Memiliki Nomor Laporan'),

                                    Checkbox::make('hanya_timeline_kosong')
                                        ->label('Timeline Belum Ada'),

                                    Checkbox::make('hanya_hari_ini')
                                        ->label('Laporan Hari Ini'),

                                ]),
                        ]),
                ])

                ->query(function (Builder $query, array $data): Builder {

                    return $query

                        ->when(
                            $data['status'] ?? null,
                            fn(Builder $query, $value)
                            => $query->where('status', $value)
                        )

                        ->when(
                            $data['jenis_insiden'] ?? null,
                            fn(Builder $query, $values)
                            => $query->whereIn('jenis_insiden', $values)
                        )

                        ->when(
                            $data['unit_kerja_id'] ?? null,
                            fn(Builder $query, $values)
                            => $query->whereIn('unit_kerja_id', $values)
                        )

                        ->when(
                            $data['dari'] ?? null,
                            fn(Builder $query, $date)
                            => $query->whereDate('tanggal_lapor', '>=', $date)
                        )

                        ->when(
                            $data['sampai'] ?? null,
                            fn(Builder $query, $date)
                            => $query->whereDate('tanggal_lapor', '<=', $date)
                        )

                        ->when(
                            $data['kelengkapan'] ?? null,
                            function (Builder $query, $value) {

                                match ($value) {

                                    'lengkap' => $query
                                        ->whereNotNull('nomor_laporan')
                                        ->whereHas('timelineEvents.entries'),

                                    'belum_lengkap' => $query
                                        ->where(function (Builder $query) {
                                            $query->whereNull('nomor_laporan')
                                                ->orWhereDoesntHave('timelineEvents.entries');
                                        }),

                                    'timeline_missing' => $query
                                        ->whereDoesntHave('timelineEvents.entries'),

                                    default => null,
                                };

                                return $query;
                            }
                        )

                        ->when(
                            $data['hanya_belum_nomor'] ?? false,
                            fn(Builder $query)
                            => $query->whereNull('nomor_laporan')
                        )

                        ->when(
                            $data['hanya_timeline_kosong'] ?? false,
                            fn(Builder $query)
                            => $query->whereDoesntHave('timelineEvents.entries')
                        )

                        ->when(
                            $data['hanya_hari_ini'] ?? false,
                            fn(Builder $query)
                            => $query->whereDate('tanggal_lapor', today())
                        );
                })
                ->columnSpanFull(),
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
