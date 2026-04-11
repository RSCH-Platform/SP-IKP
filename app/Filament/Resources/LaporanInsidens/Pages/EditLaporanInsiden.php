<?php

namespace App\Filament\Resources\LaporanInsidens\Pages;

use App\Filament\Resources\LaporanInsidens\LaporanInsidenResource;
use App\Models\LaporanInsiden;
use App\Models\User;
use App\Traits\HasWorkflowSteps;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EditLaporanInsiden extends EditRecord
{
    use HasWorkflowSteps;

    protected static string $resource = LaporanInsidenResource::class;

    protected string $view = 'filament.resources.laporan-insidens.pages.edit-laporan-insiden';

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canEdit($this->getRecord()), 404);
    }

    public function submitLaporan(): void
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

        try {
            $this->save();

            $missingFields = collect($requiredFieldsForSubmit)
                ->filter(fn($label, $field) => blank(data_get($this->record, $field)))
                ->values()
                ->all();

            if ($this->record->timelineEvents()->count() === 0) {
                $missingFields[] = 'Kronologi (Timeline)';
            }

            if (!empty($missingFields)) {
                Notification::make()
                    ->title('Laporan belum bisa dikirim')
                    ->body('Lengkapi field wajib berikut: ' . implode(', ', $missingFields))
                    ->danger()
                    ->send();

                return;
            }

            $this->record->refresh();
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

            $this->redirect(LaporanInsidenResource::getUrl('view', ['record' => $this->record->id]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal mengirim laporan')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function kembalikanLaporan(array $data): void
    {
        try {
            $this->save();

            $this->record->kembalikanKePelapor(Auth::id(), $data['alasan_pengembalian']);

            $pelapor = User::find($this->record->created_by);

            if ($pelapor) {
                Notification::make()
                    ->title('Laporan Dikembalikan untuk Revisi')
                    ->body("Laporan Anda perlu diperbaiki. Alasan: {$data['alasan_pengembalian']}")
                    ->warning()
                    ->sendToDatabase($pelapor);
            }

            Notification::make()
                ->title('Laporan dikembalikan untuk revisi')
                ->success()
                ->send();

            $this->redirect(LaporanInsidenResource::getUrl('view', ['record' => $this->record->id]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal mengembalikan laporan')
                ->danger()
                ->send();
        }
    }

    public function verifikasiLaporan(): void
    {
        try {
            $this->save();

            $missing = [];

            if (!$this->record->grading_risiko) {
                $missing[] = 'grading risiko';
            }

            if (!$this->record->catatan_tambahan) {
                $missing[] = 'catatan tambahan';
            }

            if ($missing) {
                Notification::make()
                    ->title('Data belum lengkap')
                    ->body('Harap isi: ' . implode(' dan ', $missing) . ' sebelum memverifikasi laporan.')
                    ->danger()
                    ->send();

                return;
            }

            $this->record->verifikasiLaporan(Auth::id());

            $timMutu = User::role(['tim_mutu', 'admin'])->get();

            Notification::make()
                ->title('Laporan berhasil diverifikasi')
                ->success()
                ->send();

            if ($timMutu->isNotEmpty()) {
                Notification::make()
                    ->title('Laporan Siap Investigasi')
                    ->body("Laporan dari {$this->record->nama_pelapor} telah diverifikasi dan siap untuk investigasi.")
                    ->info()
                    ->sendToDatabase($timMutu);
            }

            $this->redirect(LaporanInsidenResource::getUrl('view', ['record' => $this->record->id]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal memverifikasi laporan')
                ->danger()
                ->send();
        }
    }

    public function verifikasiUlang(array $data): void
    {
        try {
            $this->save();

            $this->record->update([
                'grading_risiko' => $data['grading_risiko'],
                'catatan_tambahan' => $data['catatan_tambahan'] ?? $this->record->catatan_tambahan,
            ]);

            $this->record->verifikasiLaporan(Auth::id());

            $timMutu = User::role(['tim_mutu', 'admin'])->get();

            Notification::make()
                ->title('Laporan diverifikasi ulang')
                ->success()
                ->send();

            if ($timMutu->isNotEmpty()) {
                Notification::make()
                    ->title('Laporan siap investigasi')
                    ->sendToDatabase($timMutu);
            }

            $this->redirect(LaporanInsidenResource::getUrl('view', ['record' => $this->record->id]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal memverifikasi ulang laporan')
                ->danger()
                ->send();
        }
    }

    public function mulaiInvestigasi(): void
    {
        try {
            $this->save();

            $this->record->mulaiInvestigasi(Auth::id());

            Notification::make()
                ->title('Investigasi dimulai')
                ->body('Tim mutu mulai pengumpulan data investigasi.')
                ->success()
                ->send();

            $this->redirect(LaporanInsidenResource::getUrl('edit', ['record' => $this->record->id]));
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Gagal memulai investigasi (validasi)', [
                'record_id' => $this->record?->id,
                'user_id' => Auth::id(),
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
            ]);

            $errors = collect($e->errors())
                ->flatten()
                ->unique()
                ->values()
                ->all();

            $message = collect($errors)
                ->map(fn($error) => is_string($error) ? $error : json_encode($error))
                ->implode(' | ');

            // If timeline is missing, make the message more user-friendly.
            $timelineErrorKeys = collect(array_keys($e->errors()))
                ->filter(fn($key) => str_contains($key, 'timeline'))
                ->all();

            if (! empty($timelineErrorKeys) || str_contains($message, 'timeline')) {
                $message = 'Silakan lengkapi Kronologi (Timeline) sebelum memulai investigasi.';
            }

            Notification::make()
                ->title('Gagal memulai investigasi')
                ->body($message)
                ->danger()
                ->send();
        } catch (\Exception $e) {
            Log::error('Gagal memulai investigasi', [
                'record_id' => $this->record?->id,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);

            Notification::make()
                ->title('Gagal memulai investigasi')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function selesaikanInvestigasi(): void
    {
        try {
            $this->save();

            $this->record->selesaikanInvestigasi(Auth::id());

            Notification::make()
                ->title('Investigasi selesai')
                ->body('Pengumpulan data investigasi telah diselesaikan.')
                ->success()
                ->send();

            $this->redirect(LaporanInsidenResource::getUrl('view', ['record' => $this->record->id]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal menyelesaikan investigasi')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(fn() => $this->record->created_by === Auth::id() && in_array($this->record->status, ['draft', 'revisi'])),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        $actions = [];
        $user = Auth::user();

        // Save button for draft/revisi/revisi_unit/investigasi statuses
        if (
            ($user?->can('Submit:LaporanInsiden') && in_array($this->record->status, [LaporanInsiden::STATUS_DRAFT, LaporanInsiden::STATUS_REVISI], true))
            || ($user?->can('Verifikasi:LaporanInsiden') && $this->record->status === LaporanInsiden::STATUS_REVISI_UNIT)
            || ($user?->can('Investigasi:LaporanInsiden') && $this->record->status === LaporanInsiden::STATUS_INVESTIGASI)
        ) {
            $actions[] = Action::make('save')
                ->label('Simpan Perubahan')
                ->color('success')
                ->icon('heroicon-o-check')
                ->action('save');
        }

        // Submit button for draft/revisi
        if (in_array($this->record->status, [LaporanInsiden::STATUS_DRAFT, LaporanInsiden::STATUS_REVISI], true) && $user?->can('Submit:LaporanInsiden')) {
            $actions[] = Action::make('submitLaporan')
                ->label($this->record->status === LaporanInsiden::STATUS_REVISI ? 'Kirim Ulang Laporan' : 'Kirim Laporan')
                ->color('warning')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->modalHeading('Kirim Laporan Insiden?')
                ->modalDescription('Laporan akan dikirim ke kepala unit untuk diverifikasi. Pastikan semua data sudah lengkap.')
                ->action('submitLaporan');
        }

        // Return/Verify buttons for dilaporkan status
        if ($this->record->status === LaporanInsiden::STATUS_DILAPORKAN && $user?->can('Verifikasi:LaporanInsiden')) {
            $actions[] = Action::make('kembalikanLaporan')
                ->label('Kembalikan untuk Revisi')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Kembalikan Laporan?')
                ->modalDescription('Laporan akan dikembalikan ke pelapor untuk diperbaiki.')
                ->schema([
                    Textarea::make('alasan_pengembalian')
                        ->label('Alasan Pengembalian')
                        ->required()
                        ->rows(4)
                        ->placeholder('Jelaskan alasan pengembalian dan apa yang perlu diperbaiki...'),
                ])
                ->action(function (array $data) {
                    $this->kembalikanLaporan($data);
                });

            $actions[] = Action::make('verifikasiLaporan')
                ->label('Verifikasi & Kirim ke Tim Mutu')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verifikasi Laporan?')
                ->modalDescription('Setelah diverifikasi, laporan akan dikirim ke tim mutu untuk investigasi.')
                ->action('verifikasiLaporan');
        }

        // Re-verify button for revisi_unit
        if ($this->record->status === LaporanInsiden::STATUS_REVISI_UNIT && $user?->can('Verifikasi:LaporanInsiden')) {
            $actions[] = Action::make('verifikasiUlang')
                ->label('Verifikasi Ulang & Kirim ke Tim Mutu')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verifikasi Ulang Laporan?')
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
                        ->rows(3)
                        ->default(fn() => $this->record->catatan_tambahan),
                ])
                ->action(function (array $data) {
                    $this->verifikasiUlang($data);
                });
        }

        // Start investigation button for investigasi status (not yet started)
        if ($this->record->status === LaporanInsiden::STATUS_INVESTIGASI && !$this->record->investigation_started_at && $user?->can('Investigasi:LaporanInsiden')) {
            $actions[] = Action::make('mulaiInvestigasi')
                ->label('Mulai Investigasi')
                ->icon('heroicon-o-play-circle')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Mulai Investigasi?')
                ->modalDescription('Investigasi akan dimulai. Tim dapat mulai mengumpulkan data investigasi.')
                ->action('mulaiInvestigasi');
        }

        // Complete investigation button for investigasi status (started but not completed)
        if ($this->record->status === LaporanInsiden::STATUS_INVESTIGASI && $this->record->investigation_started_at && !$this->record->investigation_completed_at && $user?->can('Investigasi:LaporanInsiden')) {
            $actions[] = Action::make('selesaikanInvestigasi')
                ->label('Selesaikan Investigasi')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Selesaikan Investigasi?')
                ->modalDescription('Pengumpulan data investigasi telah selesai. Laporan akan langsung masuk ke view page.')
                ->action('selesaikanInvestigasi');
        }

        // View button to redirect to view page
        // $actions[] = Action::make('viewLaporan')
        //     ->label('Lihat Laporan')
        //     ->icon('heroicon-o-eye')
        //     ->color('info')
        //     ->url(fn() => route('laporan-insiden.show', $this->record->id))
        //     ->openUrlInNewTab();

        // // Preview laporan insiden button
        // $actions[] = Action::make('previewLaporanInsiden')
        //     ->label('Preview Laporan')
        //     ->icon('heroicon-o-document-magnifying-glass')
        //     ->color('info')
        //     ->url(fn() => LaporanInsidenResource::getUrl('preview', ['record' => $this->record->id]))
        //     ->openUrlInNewTab();

        // // Preview investigasi laporan insiden button
        // $actions[] = Action::make('previewInvestigasi')
        //     ->label('Preview Investigasi')
        //     ->icon('heroicon-o-document-magnifying-glass')
        //     ->color('warning')
        //     ->url(fn() => LaporanInsidenResource::getUrl('preview-investigasi', ['record' => $this->record->id]))
        //     ->openUrlInNewTab();

        return $actions;
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

    /**
     * Prepare timeline events data for the component
     */
    public function getTimelineEventsForComponent()
    {
        // Load timeline events with relations if not already loaded
        $events = $this->record->timelineEvents;
        if (!$events->first()?->relationLoaded('entries')) {
            $events = $this->record->load('timelineEvents.entries.category')->timelineEvents;
        }
        return $this->prepareTimelineData($events);
    }

    /**
     * Helper method to prepare timeline data
     */
    private function prepareTimelineData($events)
    {
        // Group events by date
        $eventsByDate = $events->groupBy(function ($event) {
            return $event->event_datetime?->format('Y-m-d');
        })->sortKeys();

        // Extract unique categories per date
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
            'dateCategories' => $dateCategories
        ];
    }
}
