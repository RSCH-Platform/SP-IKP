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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseDraftReportsWidget extends BaseWidget implements HasTable
{
    use InteractsWithTable, HasWidgetShield;

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return $this->scopedQuery();
    }

    protected function getTableFilters(): array
    {
        return [];
    }

    protected function scopedQuery(): Builder
    {
        $query = LaporanInsiden::query();
        $user = auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($this->isSubmitterOnly($user)) {
            return $query
                ->whereIn('status', [
                    LaporanInsiden::STATUS_DRAFT,
                    LaporanInsiden::STATUS_REVISI,
                ])
                ->where('user_id', $user->getKey());
        }

        if ($this->isUnitHeadOnly($user)) {
            return $query->whereIn(
                'unit_kerja_id',
                $user->unitKerjas()->pluck('id')
            );
        }

        return $query;
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
                ->color('primary')
                ->toggleable(),

            Tables\Columns\TextColumn::make('jenis_insiden')
                ->label('Jenis Insiden')
                ->sortable()
                ->searchable()
                ->limit(40)
                ->toggleable(),

            Tables\Columns\TextColumn::make('user.name')
                ->label('Pelapor')
                ->sortable()
                ->searchable()
                ->toggleable(),

            Tables\Columns\TextColumn::make('tanggal_lapor')
                ->label('Tanggal Lapor')
                ->date('d M Y')
                ->sortable()
                ->toggleable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat Pada')
                ->dateTime('d M Y H:i')
                ->sortable()
                ->toggleable(),

            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => $this->getStatusColor($state))
                ->formatStateUsing(fn (string $state): string => $this->getStatusLabel($state))
                ->toggleable(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            ActionGroup::make([
                $this->viewAction(),
                $this->editAction(),
                $this->submitReportAction(),
                $this->verifyReportAction(),
                $this->startInvestigationAction(),
            ])
                ->button()
                ->label('Aksi')
                ->icon('heroicon-o-ellipsis-vertical'),
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

    protected function viewAction(): Action
    {
        return Action::make('view')
            ->label('Lihat')
            ->icon('heroicon-m-eye')
            ->url(fn (LaporanInsiden $record): string => LaporanInsidenResource::getUrl('view', [
                'record' => $record->id,
            ]))
            ->openUrlInNewTab(false)
            ->visible(fn (): bool => auth()->user()?->can('View:LaporanInsiden') ?? false);
    }

    protected function editAction(): Action
    {
        return Action::make('edit')
            ->label('Edit')
            ->icon('heroicon-m-pencil')
            ->url(fn (LaporanInsiden $record): string => LaporanInsidenResource::getUrl('edit', [
                'record' => $record->id,
            ]))
            ->openUrlInNewTab(false)
            ->visible(fn (LaporanInsiden $record): bool => $this->canEditReport($record));
    }

    protected function submitReportAction(): Action
    {
        return Action::make('submit_laporan')
            ->label('Kirim Laporan')
            ->icon('heroicon-o-paper-airplane')
            ->button()
            ->color('warning')
            ->visible(fn (LaporanInsiden $record): bool => $this->canSubmitReport($record))
            ->requiresConfirmation()
            ->modalHeading('Kirim Laporan Insiden?')
            ->modalDescription('Laporan akan dikirim ke kepala unit untuk diverifikasi.')
            ->action(function (LaporanInsiden $record): void {
                $this->submitReport($record);
            });
    }

    protected function verifyReportAction(): Action
    {
        return Action::make('verifikasi_laporan')
            ->label('Verifikasi')
            ->icon('heroicon-o-check-circle')
            ->button()
            ->color('success')
            ->visible(fn (LaporanInsiden $record): bool => $this->canVerifyReport($record))
            ->requiresConfirmation()
            ->modalHeading('Verifikasi Laporan?')
            ->modalDescription('Laporan akan diteruskan ke tim mutu untuk investigasi.')
            ->schema($this->getVerificationSchema())
            ->action(function (LaporanInsiden $record, array $data): void {
                $this->verifyReport($record, $data);
            });
    }

    protected function startInvestigationAction(): Action
    {
        return Action::make('mulai_investigasi')
            ->label('Mulai Investigasi')
            ->icon('heroicon-o-magnifying-glass')
            ->button()
            ->color('info')
            ->visible(fn (LaporanInsiden $record): bool => $this->canStartInvestigation($record))
            ->requiresConfirmation()
            ->modalHeading('Mulai Investigasi?')
            ->modalDescription('Investigasi akan dimulai setelah data verifikasi lengkap.')
            ->action(function (LaporanInsiden $record): void {
                $this->startInvestigation($record);
            });
    }

    protected function submitReport(LaporanInsiden $record): void
    {
        $missingFields = $this->getMissingSubmitFields($record);

        if ($missingFields !== []) {
            $this->notifyDanger(
                title: 'Laporan belum bisa dikirim',
                body: 'Lengkapi field wajib berikut: ' . implode(', ', $missingFields),
            );

            return;
        }

        $record->submitLaporan();

        $this->notifySuccess(
            title: 'Laporan berhasil dikirim',
            body: "Laporan {$record->nomor_laporan} berhasil dikirim ke kepala unit.",
        );

        $kepalaUnits = $this->getKepalaUnitRecipients($record);

        if ($kepalaUnits->isNotEmpty()) {
            Notification::make()
                ->title('Laporan Insiden Baru')
                ->body("Ada laporan insiden baru dari {$record->nama_pelapor} - {$record->nomor_laporan}")
                ->warning()
                ->sendToDatabase($kepalaUnits);
        }
    }

    protected function verifyReport(LaporanInsiden $record, array $data): void
    {
        $record->update([
            'grading_risiko' => $data['grading_risiko'],
            'catatan_tambahan' => $data['catatan_tambahan'] ?? $record->catatan_tambahan,
        ]);

        $record->verifikasiLaporan(auth()->id());

        $this->notifySuccess('Laporan berhasil diverifikasi');

        $timMutu = $this->getTimMutuRecipients();

        if ($timMutu->isNotEmpty()) {
            Notification::make()
                ->title('Laporan Siap Investigasi')
                ->body("Laporan dari {$record->nama_pelapor} telah diverifikasi dan siap untuk investigasi.")
                ->info()
                ->sendToDatabase($timMutu);
        }
    }

    protected function startInvestigation(LaporanInsiden $record): void
    {
        if (blank($record->grading_risiko)) {
            $this->notifyDanger(
                title: 'Belum bisa investigasi',
                body: 'Grading risiko wajib diisi saat verifikasi sebelum memulai investigasi.',
            );

            return;
        }

        $record->mulaiInvestigasi(auth()->id());

        $this->notifySuccess(
            title: 'Investigasi dimulai',
            body: "Laporan {$record->nomor_laporan} sekarang masuk ke tahap investigasi.",
        );
    }

    protected function getVerificationSchema(): array
    {
        return [
            ToggleButtons::make('grading_risiko')
                ->label('Grading Risiko')
                ->required()
                ->options($this->getRiskGradeOptions())
                ->colors($this->getRiskGradeColors())
                ->inline()
                ->helperText('Hanya diisi oleh Validator / Tim IKP')
                ->default(fn (LaporanInsiden $record): mixed => $record->grading_risiko),

            Textarea::make('catatan_tambahan')
                ->label('Catatan Verifikasi')
                ->rows(3)
                ->default(fn (LaporanInsiden $record): mixed => $record->catatan_tambahan),
        ];
    }

    protected function getStatusOptions(): array
    {
        return [
            LaporanInsiden::STATUS_DRAFT => 'Draft',
            LaporanInsiden::STATUS_DILAPORKAN => 'Dilaporkan',
            LaporanInsiden::STATUS_REVISI_UNIT => 'Revisi Unit',
            LaporanInsiden::STATUS_REVISI => 'Revisi',
            LaporanInsiden::STATUS_DIVERIFIKASI => 'Diverifikasi',
            LaporanInsiden::STATUS_INVESTIGASI => 'Investigasi',
        ];
    }

    protected function getStatusFilterColors(): array
    {
        return [
            LaporanInsiden::STATUS_DRAFT => 'gray',
            LaporanInsiden::STATUS_DILAPORKAN => 'info',
            LaporanInsiden::STATUS_REVISI_UNIT => 'warning',
            LaporanInsiden::STATUS_REVISI => 'danger',
            LaporanInsiden::STATUS_DIVERIFIKASI => 'success',
            LaporanInsiden::STATUS_INVESTIGASI => 'primary',
        ];
    }

    protected function getStatusColor(string $status): string
    {
        return match ($status) {
            LaporanInsiden::STATUS_DRAFT => 'gray',
            LaporanInsiden::STATUS_DILAPORKAN => 'warning',
            LaporanInsiden::STATUS_REVISI,
            LaporanInsiden::STATUS_REVISI_UNIT => 'danger',
            LaporanInsiden::STATUS_DIVERIFIKASI => 'info',
            LaporanInsiden::STATUS_INVESTIGASI => 'success',
            default => 'gray',
        };
    }

    protected function getStatusLabel(string $status): string
    {
        return $this->getStatusOptions()[$status] ?? $status;
    }

    protected function getIncidentTypeOptions(): array
    {
        return [
            'KPC (Kondisi Potensial Cedera)' => 'KPC',
            'KNC (Kejadian Nyaris Cedera)' => 'KNC',
            'KTC (Kejadian Tidak Cedera)' => 'KTC',
            'KTD (Kejadian Tidak Diharapkan)' => 'KTD',
            'Sentinel' => 'Sentinel',
        ];
    }

    protected function getUnitKerjaOptions(): array
    {
        return UnitKerja::query()
            ->orderBy('unit_name')
            ->pluck('unit_name', 'id')
            ->all();
    }

    protected function getCompletenessOptions(): array
    {
        return [
            'lengkap' => 'Lengkap',
            'belum_lengkap' => 'Belum Lengkap',
            'timeline_missing' => 'Timeline Kosong',
        ];
    }

    protected function getCompletenessColors(): array
    {
        return [
            'lengkap' => 'success',
            'belum_lengkap' => 'warning',
            'timeline_missing' => 'danger',
        ];
    }

    protected function getRiskGradeOptions(): array
    {
        return [
            'Biru' => '🔵 Biru (Tidak signifikan)',
            'Hijau' => '🟢 Hijau (Minor)',
            'Kuning' => '🟡 Kuning (Moderat)',
            'Merah' => '🔴 Merah (Mayor)',
            'Hitam' => '⚫ Hitam (Katastropik)',
        ];
    }

    protected function getRiskGradeColors(): array
    {
        return [
            'Biru' => 'info',
            'Hijau' => 'success',
            'Kuning' => 'warning',
            'Merah' => 'danger',
            'Hitam' => 'gray',
        ];
    }

    protected function canEditReport(LaporanInsiden $record): bool
    {
        return auth()->user()?->can('Update:LaporanInsiden')
            && in_array($record->status, [
                LaporanInsiden::STATUS_DRAFT,
                LaporanInsiden::STATUS_REVISI,
            ], true);
    }

    protected function canSubmitReport(LaporanInsiden $record): bool
    {
        return auth()->user()?->can('Submit:LaporanInsiden')
            && in_array($record->status, [
                LaporanInsiden::STATUS_DRAFT,
                LaporanInsiden::STATUS_REVISI,
            ], true);
    }

    protected function canVerifyReport(LaporanInsiden $record): bool
    {
        return auth()->user()?->can('Verifikasi:LaporanInsiden')
            && $record->status === LaporanInsiden::STATUS_DILAPORKAN;
    }

    protected function canStartInvestigation(LaporanInsiden $record): bool
    {
        return auth()->user()?->can('Investigasi:LaporanInsiden')
            && $record->status === LaporanInsiden::STATUS_DIVERIFIKASI;
    }

    protected function isSubmitterOnly(User $user): bool
    {
        return $user->can('Submit:LaporanInsiden')
            && !$user->can('ForceEdit:LaporanInsiden')
            && !$user->can('ViewAllData:LaporanInsiden');
    }

    protected function isUnitHeadOnly(User $user): bool
    {
        return $user->can('ForceEdit:LaporanInsiden')
            && !$user->can('ViewAllData:LaporanInsiden');
    }

    protected function getKepalaUnitRecipients(LaporanInsiden $record)
    {
        return User::role('kepala_unit')
            ->whereHas('unitKerjas', fn (Builder $query): Builder => $query->where('unit_kerja.id', $record->unit_kerja_id))
            ->get();
    }

    protected function getTimMutuRecipients()
    {
        return User::role(['tim_mutu', 'admin_ikp'])->get();
    }

    protected function getMissingSubmitFields(LaporanInsiden $record): array
    {
        $missingFields = collect($this->getRequiredSubmitFields())
            ->filter(fn (string $label, string $field): bool => blank(data_get($record, $field)))
            ->values()
            ->all();

        if (!$record->timelineEvents()->whereHas('entries')->exists()) {
            $missingFields[] = 'Kronologi (Timeline)';
        }

        return $missingFields;
    }

    protected function getRequiredSubmitFields(): array
    {
        return [
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
    }

    protected function notifySuccess(string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->title($title)
            ->success();

        if ($body) {
            $notification->body($body);
        }

        $notification->send();
    }

    protected function notifyDanger(string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->title($title)
            ->danger();

        if ($body) {
            $notification->body($body);
        }

        $notification->send();
    }
}
