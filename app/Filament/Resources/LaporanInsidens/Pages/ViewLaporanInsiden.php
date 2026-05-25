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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class ViewLaporanInsiden extends ViewRecord
{
    use HasWorkflowSteps;

    protected static string $resource = LaporanInsidenResource::class;

    protected string $view = 'filament.resources.laporan-insidens.pages.view-laporan-insiden';

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getQuickActionGroup(),

            EditAction::make()
                ->label(fn() => $this->getEditActionLabel())
                ->icon(fn() => $this->getEditActionIcon())
                ->visible(fn() => $this->canEdit()),
        ];
    }

    protected function getQuickActionGroup(): ActionGroup
    {
        return ActionGroup::make(array_filter([
            $this->getSubmitAction(),
            $this->getVerifyAction(),
            $this->getReturnAction(),
            $this->getStartInvestigationAction(),
            $this->getReopenInvestigationAction(),
        ]))
            ->label('Aksi Cepat')
            ->icon('heroicon-o-bolt')
            ->color('primary')
            ->button();
    }

    protected function getReopenInvestigationAction(): ?Action
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $visible = $user->can('Investigasi:LaporanInsiden')
            && $this->record->status === LaporanInsiden::STATUS_SELESAI;

        if (! $visible) {
            return null;
        }

        return Action::make('reopen_investigation')
            ->label('Buka Kembali Investigasi')
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Buka kembali investigasi?')
            ->modalDescription('Mengembalikan laporan ke status Investigasi sehingga tim dapat melanjutkan investigasi.')
            ->modalSubmitActionLabel('Buka Kembali')
            ->action(fn() => $this->reopenInvestigasi());
    }

    protected function reopenInvestigasi(): void
    {
        try {
            $this->record->reopenInvestigation(auth()->id());

            Notification::make()
                ->title('Investigasi dibuka kembali')
                ->body('Laporan kembali ke status Investigasi dan dapat dilanjutkan oleh tim.')
                ->success()
                ->send();

            $this->redirectToEditPage();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal membuka kembali investigasi')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getSubmitAction(): Action
    {
        return Action::make('submit_laporan')
            ->label('Kirim ke Verifikasi')
            ->icon('heroicon-o-paper-airplane')
            ->color('warning')
            ->visible(
                fn() =>
                $this->canDo('Submit:LaporanInsiden', [
                    LaporanInsiden::STATUS_DRAFT,
                    LaporanInsiden::STATUS_REVISI,
                ])
                && $this->canEdit()
            )
            ->requiresConfirmation()
            ->modalHeading('Kirim Laporan Insiden')
            ->modalDescription('Laporan akan dikirim ke kepala unit untuk proses verifikasi. Pastikan seluruh data sudah lengkap.')
            ->modalSubmitActionLabel('Kirim Laporan')
            ->action(fn() => $this->submitLaporan());
    }

    protected function getVerifyAction(): Action
    {
        return Action::make('verifikasi_laporan')
            ->label('Verifikasi & Teruskan')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(
                fn() =>
                $this->canDo(
                    'Verifikasi:LaporanInsiden',
                    LaporanInsiden::STATUS_DILAPORKAN,
                )
            )
            ->schema([
                $this->getGradingRisikoField(),

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
            ->action(fn(array $data) => $this->verifikasiLaporan($data));
    }

    protected function getReturnAction(): Action
    {
        return Action::make('kembalikan_laporan')
            ->label('Kembalikan ke Pelapor')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('danger')
            ->visible(
                fn() =>
                $this->canDo(
                    'Kembalikan:LaporanInsiden',
                    LaporanInsiden::STATUS_DILAPORKAN,
                )
            )
            ->requiresConfirmation()
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
            ->action(fn(array $data) => $this->kembalikanLaporan($data));
    }

    protected function getStartInvestigationAction(): Action
    {
        return Action::make('mulai_investigasi')
            ->label('Mulai Investigasi')
            ->icon('heroicon-o-magnifying-glass')
            ->color('info')
            ->visible(
                fn($record) =>
                auth()->user()?->can('Investigasi:LaporanInsiden')
                && $record->status === LaporanInsiden::STATUS_DIVERIFIKASI
            )
            ->requiresConfirmation()
            ->modalHeading('Mulai Investigasi')
            ->modalDescription('Laporan akan masuk ke tahap investigasi dan tim akan memulai proses investigasi laporan ini.')
            ->modalSubmitActionLabel('Mulai Investigasi')
            ->action(fn() => $this->mulaiInvestigasi());
    }

    protected function canDo(string $permission, array|string $statuses): bool
    {
        return auth()->user()?->can($permission)
            && in_array($this->record->status, (array) $statuses, true);
    }

    protected function canEdit(): bool
    {
        $user = auth()->user();
        $status = $this->record->status;

        if (!$user) {
            return false;
        }

        if ($user->can('ViewAllData:LaporanInsiden')) {
            return true;
        }

        if (
            in_array($status, [
                LaporanInsiden::STATUS_DRAFT,
                LaporanInsiden::STATUS_REVISI,
            ], true)
        ) {
            return $user->id === $this->record->user_id;
        }

        if ($status === LaporanInsiden::STATUS_DILAPORKAN) {
            return $user->can('Verifikasi:LaporanInsiden');
        }

        if (
            in_array($status, [
                LaporanInsiden::STATUS_DIVERIFIKASI,
                LaporanInsiden::STATUS_REVISI_UNIT,
                LaporanInsiden::STATUS_INVESTIGASI,
            ], true)
        ) {
            return $user->can('Investigasi:LaporanInsiden');
        }

        return false;
    }

    protected function submitLaporan(): void
    {
        $this->record->submitLaporan();

        $this->notifyKepalaUnit();

        $this->notifySuccess('Laporan berhasil dikirim');

        $this->redirectToViewPage();
    }

    protected function verifikasiLaporan(array $data): void
    {
        $this->record->update([
            'grading_risiko' => $data['grading_risiko'] ?? null,
            'catatan_tambahan' => $data['catatan_tambahan'] ?? null,
        ]);

        $this->record->refresh();

        $this->record->verifikasiLaporan(auth()->id());

        $this->notifyTimMutuAfterVerification();

        $this->notifySuccess('Laporan berhasil diverifikasi');

        $this->redirectToViewPage();
    }

    protected function kembalikanLaporan(array $data): void
    {
        $reason = $data['rejection_reason'];

        $this->record->kembalikanKePelapor(
            auth()->id(),
            $reason,
        );

        $this->notifyPelaporForRevision($reason);

        $this->notifySuccess('Laporan dikembalikan ke pelapor');

        $this->redirectToViewPage();
    }

    protected function mulaiInvestigasi(): void
    {
        if (blank($this->record->grading_risiko)) {
            Notification::make()
                ->title('Belum bisa investigasi')
                ->body('Grading risiko wajib diisi saat verifikasi sebelum memulai investigasi.')
                ->danger()
                ->send();

            return;
        }

        $this->record->mulaiInvestigasi(auth()->id());

        Notification::make()
            ->title('Investigasi dimulai')
            ->body("Laporan dari {$this->record->nama_pelapor} sekarang masuk ke tahap investigasi.")
            ->success()
            ->send();

        $this->redirectToEditPage();
    }

    public function getGroupedInvestigationData(): array
    {
        $categories = [
            'interview' => [
                'label' => '👤 Interview',
                'items' => [],
            ],
            'review_dokumen' => [
                'label' => '📄 Review Dokumen',
                'items' => [],
            ],
            'observasi' => [
                'label' => '👁️ Observasi',
                'items' => [],
            ],
        ];

        $investigationData = $this->record
            ->investigationData()
            ->with('creator')
            ->get();

        foreach ($investigationData as $item) {
            $category = trim($item->kategori ?? 'interview');

            if (isset($categories[$category])) {
                $categories[$category]['items'][] = $item;
            }
        }

        return array_filter(
            $categories,
            fn(array $category) => !empty($category['items']),
        );
    }

    public function getTimelineEventsForComponent(): array
    {
        $events = $this->record->relationLoaded('timelineEvents')
            ? $this->record->timelineEvents
            : $this->record
                ->timelineEvents()
                ->with('entries.category')
                ->orderBy('event_datetime', 'asc')
                ->get();

        return $this->prepareTimelineData($events);
    }

    private function prepareTimelineData($events): array
    {
        $eventsByDate = $events
            ->groupBy(
                fn($event) =>
                $event->event_datetime?->format('Y-m-d')
            )
            ->sortKeys();

        $dateCategories = [];

        foreach ($eventsByDate as $date => $dateEvents) {
            $dateCategories[$date] = $dateEvents
                ->flatMap(fn($event) => $event->entries ?? [])
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
            ->components(LaporanInsidenInfolistSchema::sections())
            ->columns(1);
    }

    private function getGradingRisikoField(): ToggleButtons
    {
        return ToggleButtons::make('grading_risiko')
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
            ->default(fn() => $this->record->grading_risiko);
    }

    private function getEditActionLabel(): string
    {
        return match ($this->record->status) {
            LaporanInsiden::STATUS_DRAFT => 'Lengkapi Laporan',
            LaporanInsiden::STATUS_REVISI => 'Perbaiki Laporan',
            LaporanInsiden::STATUS_DILAPORKAN => 'Lihat Laporan',
            LaporanInsiden::STATUS_DIVERIFIKASI => 'Tinjau Laporan',
            LaporanInsiden::STATUS_INVESTIGASI => 'Proses Investigasi',
            LaporanInsiden::STATUS_SELESAI => 'Detail Kasus',
            default => 'Edit Laporan',
        };
    }

    private function getEditActionIcon(): string
    {
        return match ($this->record->status) {
            LaporanInsiden::STATUS_DRAFT => 'heroicon-o-pencil',
            LaporanInsiden::STATUS_REVISI => 'heroicon-o-pencil-square',
            LaporanInsiden::STATUS_DILAPORKAN => 'heroicon-o-eye',
            LaporanInsiden::STATUS_DIVERIFIKASI => 'heroicon-o-document-text',
            LaporanInsiden::STATUS_INVESTIGASI => 'heroicon-o-folder-open',
            default => 'heroicon-o-pencil-square',
        };
    }

    private function notifyKepalaUnit(): void
    {
        $kepalaUnits = User::role('kepala_unit')->get();

        if ($kepalaUnits->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('Laporan Insiden Baru')
            ->body("Ada laporan insiden baru dari {$this->record->nama_pelapor} yang perlu diverifikasi.")
            ->warning()
            ->sendToDatabase($kepalaUnits);
    }

    private function notifyTimMutuAfterVerification(): void
    {
        $users = User::role(['tim_mutu', 'admin_ikp'])->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('Laporan Insiden Diverifikasi')
            ->body("Laporan dari {$this->record->nama_pelapor} telah diverifikasi oleh " . auth()->user()->name . '.')
            ->success()
            ->sendToDatabase($users);
    }

    private function notifyPelaporForRevision(string $reason): void
    {
        if (!$this->record->user) {
            return;
        }

        Notification::make()
            ->title('Laporan Perlu Diperbaiki')
            ->body("Laporan insiden Anda perlu diperbaiki. Alasan: {$reason}")
            ->danger()
            ->sendToDatabase([$this->record->user]);
    }

    private function notifySuccess(string $title): void
    {
        Notification::make()
            ->title($title)
            ->success()
            ->send();
    }

    private function redirectToViewPage(): void
    {
        $this->redirect(
            static::getResource()::getUrl('view', [
                'record' => $this->record,
            ])
        );
    }

    private function redirectToEditPage(): void
    {
        $this->redirect(
            static::getResource()::getUrl('edit', [
                'record' => $this->record,
            ])
        );
    }
}