<?php

namespace App\Filament\Resources\LaporanInsidens\Pages;

use App\Filament\Resources\LaporanInsidens\LaporanInsidenResource;
use App\Filament\Resources\LaporanInsidens\Schemas\LaporanInsidenInfolistSchema;
use App\Models\LaporanInsiden;
use App\Models\User;
use App\Traits\HasWorkflowSteps;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;


class ViewLaporanInsiden extends ViewRecord
{
    use HasWorkflowSteps;

    protected static string $resource = LaporanInsidenResource::class;

    protected string $view = 'filament.resources.laporan-insidens.pages.view-laporan-insiden';


    protected function getHeaderActions(): array
    {
        return [

            ActionGroup::make([

                Action::make('submit_laporan')
                    ->label('Kirim ke Verifikasi')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn() => $this->canDo(
                        'Submit:LaporanInsiden',
                        [
                            LaporanInsiden::STATUS_DRAFT,
                            LaporanInsiden::STATUS_REVISI,
                        ]
                    ) && $this->canEdit())
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Laporan Insiden')
                    ->modalDescription('Laporan akan dikirim ke kepala unit untuk proses verifikasi. Pastikan seluruh data sudah lengkap.')
                    ->modalSubmitActionLabel('Kirim Laporan')
                    ->action(fn() => $this->submitLaporan()),


                Action::make('verifikasi_laporan')
                    ->label('Verifikasi & Teruskan')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn() => $this->canDo(
                        'Verifikasi:LaporanInsiden',
                        LaporanInsiden::STATUS_DILAPORKAN
                    ))
                    ->schema([
                        ToggleButtons::make('grading_risiko')
                            ->label('Grading Risiko')
                            ->required()
                            ->options([
                                'Biru'   => '🔵 Biru (Tidak signifikan)',
                                'Hijau'  => '🟢 Hijau (Minor)',
                                'Kuning' => '🟡 Kuning (Moderat)',
                                'Merah'  => '🔴 Merah (Mayor)',
                                'Hitam'  => '⚫ Hitam (Katastropik)',
                            ])
                            ->colors([
                                'Biru'   => 'info',
                                'Hijau'  => 'success',
                                'Kuning' => 'warning',
                                'Merah'  => 'danger',
                                'Hitam'  => 'gray',
                            ])
                            ->inline()
                            ->helperText('Hanya diisi oleh Validator / Tim IKP')
                            ->default(fn() => $this->record->grading_risiko),
                        Textarea::make('catatan_tambahan')
                            ->label('Catatan Verifikasi')
                            ->hidden()
                            ->rows(3)
                            ->default(fn() => $this->record->catatan_tambahan),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Laporan')
                    ->modalDescription('Laporan akan diverifikasi dan diteruskan ke tim mutu untuk investigasi.')
                    ->modalSubmitActionLabel('Verifikasi')
                    ->action(fn(array $data) => $this->verifikasiLaporan($data)),


                Action::make('kembalikan_laporan')
                    ->label('Kembalikan ke Pelapor')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn() => $this->canDo(
                        'Kembalikan:LaporanInsiden',
                        LaporanInsiden::STATUS_DILAPORKAN
                    ))
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Alasan Pengembalian')
                            ->placeholder('Tuliskan apa yang perlu diperbaiki oleh pelapor...')
                            ->required()
                            ->minLength(10)
                            ->rows(4),
                    ])
                    ->modalHeading('Kembalikan Laporan')
                    ->modalDescription('Laporan akan dikembalikan ke pelapor untuk diperbaiki.')
                    ->modalSubmitActionLabel('Kembalikan')
                    ->action(fn(array $data) => $this->kembalikanLaporan($data)),


                Action::make('mulai_investigasi')
                    ->label('Mulai Investigasi')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('info')
                    ->visible(
                        fn($record) =>
                        auth()->user()?->can('Investigasi:LaporanInsiden') &&
                            $record->status === LaporanInsiden::STATUS_DIVERIFIKASI
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Mulai Investigasi')
                    ->modalDescription('Laporan akan masuk ke tahap investigasi dan tim akan memulai proses investigasi laporan ini.')
                    ->modalSubmitActionLabel('Mulai Investigasi')
                    ->action(fn() => $this->mulaiInvestigasi()),
            ])
                ->label('Aksi Cepat')
                ->icon('heroicon-o-bolt')
                ->color('primary')
                ->button(),




            EditAction::make()
                ->label(fn() => match ($this->record->status) {

                    LaporanInsiden::STATUS_DRAFT => 'Lengkapi Laporan',

                    LaporanInsiden::STATUS_REVISI => 'Perbaiki Laporan',

                    LaporanInsiden::STATUS_DILAPORKAN => 'Lihat Laporan',

                    LaporanInsiden::STATUS_DIVERIFIKASI => 'Tinjau Laporan',

                    LaporanInsiden::STATUS_INVESTIGASI => 'Process Investigasi',

                    default => 'Edit Laporan',
                })
                ->icon(fn() => match ($this->record->status) {

                    LaporanInsiden::STATUS_DRAFT => 'heroicon-o-pencil',

                    LaporanInsiden::STATUS_REVISI => 'heroicon-o-pencil-square',

                    LaporanInsiden::STATUS_DILAPORKAN => 'heroicon-o-eye',

                    LaporanInsiden::STATUS_DIVERIFIKASI => 'heroicon-o-document-text',

                    LaporanInsiden::STATUS_INVESTIGASI => 'heroicon-o-folder-open',

                    default => 'heroicon-o-pencil-square',
                })
                ->visible(fn() => $this->canEdit()),

        ];
    }

    protected function canDo(string $permission, array|string $statuses): bool
    {
        $user = auth()->user();
        $statuses = (array) $statuses;

        return $user?->can($permission)
            && in_array($this->record->status, $statuses);
    }

    protected function canEdit(): bool
    {
        $user = auth()->user();
        $status = $this->record->status;

        if (!$user) {
            return false;
        }

        // Admin / super access
        if ($user->can('ViewAllData:LaporanInsiden')) {
            return true;
        }

        // Pelapor boleh edit saat draft / revisi
        if (in_array($status, [
            LaporanInsiden::STATUS_DRAFT,
            LaporanInsiden::STATUS_REVISI,
        ])) {
            return $user->id === $this->record->user_id;
        }

        // Kepala unit boleh edit saat dilaporkan
        if ($status === LaporanInsiden::STATUS_DILAPORKAN) {
            return $user->can('Verifikasi:LaporanInsiden');
        }

        // Tim mutu boleh edit saat diverifikasi / revisi unit
        if (in_array($status, [
            LaporanInsiden::STATUS_DIVERIFIKASI,
            LaporanInsiden::STATUS_REVISI_UNIT,
            LaporanInsiden::STATUS_INVESTIGASI,
        ])) {
            return $user->can('Investigasi:LaporanInsiden');
        }

        return false;
    }

    protected function submitLaporan(): void
    {
        $this->record->submitLaporan();

        $kepalaUnits = User::role('kepala_unit')->get();

        if ($kepalaUnits->isNotEmpty()) {
            Notification::make()
                ->title('Laporan Insiden Baru')
                ->body("Ada laporan insiden baru dari {$this->record->nama_pelapor} yang perlu diverifikasi.")
                ->warning()
                ->sendToDatabase($kepalaUnits);
        }

        Notification::make()
            ->title('Laporan berhasil dikirim')
            ->success()
            ->send();

        $this->redirect(static::getResource()::getUrl('view', [
            'record' => $this->record
        ]));
    }

    protected function verifikasiLaporan(array $data): void
    {
        // Simpan grading risiko dan catatan terlebih dahulu
        $this->record->update([
            'grading_risiko' => $data['grading_risiko'] ?? null,
            'catatan_tambahan' => $data['catatan_tambahan'] ?? null,
        ]);

        // Refresh instance untuk mendapatkan data terbaru
        $this->record->refresh();

        // Sekarang verifikasi
        $this->record->verifikasiLaporan(auth()->id());

        $notifyUsers = User::role(['tim_mutu', 'admin_ikp'])->get();

        if ($notifyUsers->isNotEmpty()) {
            Notification::make()
                ->title('Laporan Insiden Diverifikasi')
                ->body("Laporan dari {$this->record->nama_pelapor} telah diverifikasi oleh " . auth()->user()->name . '.')
                ->success()
                ->sendToDatabase($notifyUsers);
        }

        Notification::make()
            ->title('Laporan berhasil diverifikasi')
            ->success()
            ->send();

        $this->redirect(static::getResource()::getUrl('view', [
            'record' => $this->record
        ]));
    }

    protected function kembalikanLaporan(array $data): void
    {
        $this->record->kembalikanKePelapor(
            auth()->id(),
            $data['rejection_reason']
        );

        if ($this->record->user) {
            Notification::make()
                ->title('Laporan Perlu Diperbaiki')
                ->body("Laporan insiden Anda perlu diperbaiki. Alasan: {$data['rejection_reason']}")
                ->danger()
                ->sendToDatabase([$this->record->user]);
        }

        Notification::make()
            ->title('Laporan dikembalikan ke pelapor')
            ->success()
            ->send();

        $this->redirect(static::getResource()::getUrl('view', [
            'record' => $this->record
        ]));
    }

    protected function mulaiInvestigasi(): void
    {
        // Validasi grading risiko
        if (blank($this->record->grading_risiko)) {
            Notification::make()
                ->title('Belum bisa investigasi')
                ->body('Grading risiko wajib diisi saat verifikasi sebelum memulai investigasi.')
                ->danger()
                ->send();

            return;
        }

        // Mulai investigasi
        $this->record->mulaiInvestigasi(auth()->id());

        Notification::make()
            ->title('Investigasi dimulai')
            ->body("Laporan dari {$this->record->nama_pelapor} sekarang masuk ke tahap investigasi.")
            ->success()
            ->send();

        $this->redirect(static::getResource()::getUrl('edit', [
            'record' => $this->record
        ]));
    }

    public function getGroupedInvestigationData(): array
    {
        $categories = [
            'interview' => ['label' => '👤 Interview', 'items' => []],
            'review_dokumen' => ['label' => '📄 Review Dokumen', 'items' => []],
            'observasi' => ['label' => '👁️ Observasi', 'items' => []],
        ];

        $investigationData = $this->record->investigationData()
            ->with(['creator'])
            ->get();

        foreach ($investigationData as $item) {
            $kategori = trim($item->kategori ?? 'interview');
            if (isset($categories[$kategori])) {
                $categories[$kategori]['items'][] = $item;
            }
        }

        return array_filter($categories, fn($cat) => !empty($cat['items']));
    }

    public function getTimelineEventsForComponent()
    {
        $events = $this->record->relationLoaded('timelineEvents')
            ? $this->record->timelineEvents
            : $this->record->timelineEvents()
            ->with(['entries.category'])
            ->orderBy('event_datetime', 'asc')
            ->get();

        return $this->prepareTimelineData($events);
    }

    private function prepareTimelineData($events): array
    {
        $eventsByDate = $events->groupBy(function ($event) {
            return $event->event_datetime?->format('Y-m-d');
        })->sortKeys();

        $dateCategories = [];
        foreach ($eventsByDate as $date => $dateEvents) {
            $dateCategories[$date] = $dateEvents->flatMap(fn($event) => $event->entries ?? [])
                ->pluck('category')
                ->unique('id')
                ->sortBy('sort_order')
                ->values();
        }

        return [
            'eventsByDate' => $eventsByDate,
            'dateCategories' => $dateCategories,
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components(LaporanInsidenInfolistSchema::sections())->columns(1);
    }
}
