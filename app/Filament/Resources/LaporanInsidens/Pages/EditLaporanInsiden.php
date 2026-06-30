<?php

namespace App\Filament\Resources\LaporanInsidens\Pages;

use App\Actions\LaporanInsiden\SubmitLaporanAction;
use App\Actions\LaporanInsiden\VerifikasiLaporanAction;
use App\Actions\LaporanInsiden\KembalikanLaporanAction;
use App\Actions\LaporanInsiden\MulaiInvestigasiAction;
use App\Actions\LaporanInsiden\SelesaikanInvestigasiAction;
use App\Actions\LaporanInsiden\ReopenInvestigasiAction;
use App\Filament\Resources\LaporanInsidens\LaporanInsidenResource;
use App\Models\LaporanInsiden;
use App\Models\User;
use App\Traits\HasWorkflowSteps;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EditLaporanInsiden extends EditRecord
{
    use HasWorkflowSteps;

    protected static string $resource = LaporanInsidenResource::class;

    protected string $view = 'filament.resources.laporan-insidens.pages.edit-laporan-insiden';

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canEdit($this->getRecord()), 404);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->load('investigationData');

        $this->forgetInvestigationCountsCache();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->forgetInvestigationCountsCache();

        return $data;
    }

    public function submitLaporan(): void
    {

        try {
            $this->save(false); // Do not redirect yet!

            $missingFields = $this->getMissingSubmitFields();

            if (!empty($missingFields)) {
                dd($missingFields);
                $this->notifyDanger(
                    title: 'Laporan belum bisa dikirim',
                    body: 'Lengkapi field wajib berikut: ' . implode(', ', $missingFields),
                );

                return;
            }

            $this->record->refresh();
            app(SubmitLaporanAction::class)->execute($this->record, Auth::id());

            $this->notifySuccess('Laporan berhasil dikirim');

            $this->redirectToViewPage();
        } catch (\Filament\Support\Exceptions\Halt $e) {
            throw $e;
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->notifyDanger(
                title: 'Gagal mengirim laporan',
                body: $e->getMessage(),
            );
        }
    }

    public function kembalikanLaporan(array $data): void
    {
        try {
            $this->save();

            app(KembalikanLaporanAction::class)->execute(
                $this->record,
                Auth::id(),
                $data['alasan_pengembalian'],
                false // not to kepala unit
            );

            $this->notifySuccess('Laporan dikembalikan untuk revisi');

            $this->redirectToViewPage();
        } catch (\Exception $e) {
            $this->notifyDanger('Gagal mengembalikan laporan');
        }
    }

    public function verifikasiLaporan(): void
    {
        try {
            $this->save();

            if (!$this->record->grading_risiko) {
                $this->notifyDanger(
                    title: 'Data belum lengkap',
                    body: 'Harap isi: grading risiko sebelum memverifikasi laporan.',
                );

                return;
            }

            app(VerifikasiLaporanAction::class)->execute(
                $this->record,
                Auth::id(),
                $this->record->grading_risiko
            );

            $this->notifySuccess('Laporan berhasil diverifikasi');

            $this->redirectToViewPage();
        } catch (\Exception $e) {
            $this->notifyDanger('Gagal memverifikasi laporan');
        }
    }

    public function verifikasiUlang(array $data): void
    {
        try {
            $this->save();

            app(VerifikasiLaporanAction::class)->execute(
                $this->record,
                Auth::id(),
                $data['grading_risiko'],
                $data['catatan_tambahan'] ?? $this->record->catatan_tambahan
            );

            $this->notifySuccess('Laporan diverifikasi ulang');

            $this->redirectToViewPage();
        } catch (\Exception $e) {
            $this->notifyDanger('Gagal memverifikasi ulang laporan');
        }
    }

    public function mulaiInvestigasi(): void
    {
        try {
            $this->save();

            app(MulaiInvestigasiAction::class)->execute($this->record, Auth::id());

            Notification::make()
                ->title('Investigasi dimulai')
                ->body('Tim mutu mulai pengumpulan data investigasi.')
                ->success()
                ->send();

            $this->redirectToEditPage();
        } catch (ValidationException $e) {
            $this->handleStartInvestigationValidationException($e);
        } catch (\Exception $e) {
            Log::error('Gagal memulai investigasi', [
                'record_id' => $this->record?->id,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);

            $this->notifyDanger(
                title: 'Gagal memulai investigasi',
                body: $e->getMessage(),
            );
        }
    }

    public function selesaikanInvestigasi(): void
    {
        try {
            $this->save();

            app(SelesaikanInvestigasiAction::class)->execute($this->record, Auth::id());

            Notification::make()
                ->title('Investigasi selesai')
                ->body('Pengumpulan data investigasi telah diselesaikan.')
                ->success()
                ->send();

            $this->redirectToViewPage();
        } catch (\Exception $e) {
            $this->notifyDanger(
                title: 'Gagal menyelesaikan investigasi',
                body: $e->getMessage(),
            );
        }
    }

    public function reopenInvestigasi(): void
    {
        try {
            $this->save();

            app(ReopenInvestigasiAction::class)->execute($this->record, Auth::id());

            Notification::make()
                ->title('Investigasi dibuka kembali')
                ->body('Laporan kembali ke status Investigasi dan dapat dilanjutkan oleh tim.')
                ->success()
                ->send();

            $this->redirectToEditPage();
        } catch (\Exception $e) {
            $this->notifyDanger(
                title: 'Gagal membuka kembali investigasi',
                body: $e->getMessage(),
            );
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ...$this->getInvestigationActions(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            ...$this->getSaveActions(),
            ...$this->getSubmitActions(),
            ...$this->getVerificationActions(),
        ];
    }

    private function getSaveActions(): array
    {
        $user = Auth::user();

        $canSaveAsPelapor = $user?->can('Submit:LaporanInsiden')
            && in_array($this->record->status, [
                LaporanInsiden::STATUS_DRAFT,
                LaporanInsiden::STATUS_REVISI,
            ], true);

        $canSaveAsValidator = $user?->can('Verifikasi:LaporanInsiden')
            && $this->record->status === LaporanInsiden::STATUS_REVISI_UNIT;

        $canSaveAsInvestigator = $user?->can('Investigasi:LaporanInsiden')
            && $this->record->status === LaporanInsiden::STATUS_INVESTIGASI;

        if (!($canSaveAsPelapor || $canSaveAsValidator || $canSaveAsInvestigator)) {
            return [];
        }

        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action('save'),
        ];
    }

    private function getSubmitActions(): array
    {
        $user = Auth::user();

        $canSubmit = $user?->can('Submit:LaporanInsiden')
            && in_array($this->record->status, [
                LaporanInsiden::STATUS_DRAFT,
                LaporanInsiden::STATUS_REVISI,
            ], true);

        if (!$canSubmit) {
            return [];
        }

        return [
            Action::make('submitLaporan')
                ->label(
                    $this->record->status === LaporanInsiden::STATUS_REVISI
                    ? 'Kirim Ulang Laporan'
                    : 'Kirim Laporan'
                )
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Kirim Laporan Insiden?')
                ->modalDescription('Laporan akan dikirim ke kepala unit untuk diverifikasi. Pastikan semua data sudah lengkap.')
                ->action('submitLaporan'),
        ];
    }

    private function getVerificationActions(): array
    {
        return [
            ...$this->getVerifyReportActions(),
            ...$this->getReverifyReportActions(),
        ];
    }

    private function getVerifyReportActions(): array
    {
        $user = Auth::user();

        $canVerify = $user?->can('Verifikasi:LaporanInsiden')
            && $this->record->status === LaporanInsiden::STATUS_DILAPORKAN;

        if (!$canVerify) {
            return [];
        }

        return [
            Action::make('verifikasiLaporan')
                ->label('Verifikasi & Kirim ke Tim Mutu')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verifikasi Laporan?')
                ->modalDescription('Setelah diverifikasi, laporan akan dikirim ke tim mutu untuk investigasi.')
                ->action('verifikasiLaporan'),
        ];
    }

    private function getReverifyReportActions(): array
    {
        $user = Auth::user();

        $canReverify = $user?->can('Verifikasi:LaporanInsiden')
            && $this->record->status === LaporanInsiden::STATUS_REVISI_UNIT;

        if (!$canReverify) {
            return [];
        }

        return [
            Action::make('verifikasiUlang')
                ->label('Verifikasi Ulang & Kirim ke Tim Mutu')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verifikasi Ulang Laporan?')
                ->schema([
                    $this->getGradingRisikoField(),

                    Textarea::make('catatan_tambahan')
                        ->label('Catatan Verifikasi')
                        ->rows(3)
                        ->default(fn() => $this->record->catatan_tambahan),
                ])
                ->action(fn(array $data) => $this->verifikasiUlang($data)),
        ];
    }

    private function getInvestigationActions(): array
    {
        return [
            ...$this->getStartInvestigationActions(),
            ...$this->getCompleteInvestigationActions(),
            ...$this->getReopenInvestigationAction(),
        ];
    }

    private function getReopenInvestigationAction(): array
    {
        $user = Auth::user();

        $canReopen = $user?->can('Investigasi:LaporanInsiden')
            && $this->record->status === LaporanInsiden::STATUS_SELESAI;

        if (!$canReopen) {
            return [];
        }

        return [
            Action::make('reopenInvestigasi')
                ->label('Buka Kembali Investigasi')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Buka kembali investigasi?')
                ->modalDescription('Mengembalikan laporan ke status Investigasi. Hati-hati: perubahan ini memungkinkan tim melanjutkan investigasi.')
                ->action('reopenInvestigasi'),
        ];
    }

    private function getStartInvestigationActions(): array
    {
        $user = Auth::user();

        $canStartInvestigation = $user?->can('Investigasi:LaporanInsiden')
            && in_array($this->record->status, [
                LaporanInsiden::STATUS_INVESTIGASI,
                LaporanInsiden::STATUS_DIVERIFIKASI,
            ], true)
            && !$this->record->investigation?->investigation_started_at;

        if (!$canStartInvestigation) {
            return [];
        }

        return [
            Action::make('mulaiInvestigasi')
                ->label('Mulai Investigasi')
                ->icon('heroicon-o-play-circle')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Mulai Investigasi?')
                ->modalDescription('Investigasi akan dimulai. Tim dapat mulai mengumpulkan data investigasi.')
                ->action('mulaiInvestigasi'),
        ];
    }

    private function getCompleteInvestigationActions(): array
    {
        $user = Auth::user();

        $canCompleteInvestigation = $user?->can('Investigasi:LaporanInsiden')
            && $this->record->status === LaporanInsiden::STATUS_INVESTIGASI
            && $this->record->investigation?->investigation_started_at
            && !$this->record->investigation?->investigation_completed_at;

        if (!$canCompleteInvestigation) {
            return [];
        }

        return [
            Action::make('selesaikanInvestigasi')
                ->label('Selesaikan Investigasi')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Selesaikan Investigasi?')
                ->modalDescription('Pengumpulan data investigasi telah selesai. Laporan akan langsung masuk ke view page.')
                ->action('selesaikanInvestigasi'),
        ];
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
        $events = $this->record->timelineEvents;

        if (!$events->first()?->relationLoaded('entries')) {
            $events = $this->record
                ->load('timelineEvents.entries.category')
                ->timelineEvents;
        }

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

    private function getMissingSubmitFields(): array
    {
        $requiredFields = [
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

        $missingFields = collect($requiredFields)
            ->filter(fn($label, $field) => blank(data_get($this->record, $field)))
            ->values()
            ->all();

        if ($this->record->timelineEvents()->count() === 0) {
            $missingFields[] = 'Kronologi (Timeline)';
        }

        return $missingFields;
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



    private function handleStartInvestigationValidationException(ValidationException $e): void
    {
        Log::warning('Gagal memulai investigasi (validasi)', [
            'record_id' => $this->record?->id,
            'user_id' => Auth::id(),
            'errors' => $e->errors(),
            'message' => $e->getMessage(),
        ]);

        $message = collect($e->errors())
            ->flatten()
            ->unique()
            ->values()
            ->map(fn($error) => is_string($error) ? $error : json_encode($error))
            ->implode(' | ');

        $hasTimelineError = collect(array_keys($e->errors()))
            ->contains(fn($key) => str_contains($key, 'timeline'));

        if ($hasTimelineError || str_contains($message, 'timeline')) {
            $message = 'Silakan lengkapi Kronologi (Timeline) sebelum memulai investigasi.';
        }

        $this->notifyDanger(
            title: 'Gagal memulai investigasi',
            body: $message,
        );
    }

    private function notifySuccess(string $title): void
    {
        Notification::make()
            ->title($title)
            ->success()
            ->send();
    }

    private function notifyDanger(string $title, ?string $body = null): void
    {
        Notification::make()
            ->title($title)
            ->when($body, fn(Notification $notification) => $notification->body($body))
            ->danger()
            ->send();
    }

    private function redirectToViewPage(): void
    {
        $this->redirect(
            LaporanInsidenResource::getUrl('view', [
                'record' => $this->record->id,
            ])
        );
    }

    private function redirectToEditPage(): void
    {
        $this->redirect(
            LaporanInsidenResource::getUrl('edit', [
                'record' => $this->record->id,
            ])
        );
    }

    private function forgetInvestigationCountsCache(): void
    {
        Cache::forget("investigation_counts_{$this->record->id}");
    }
}