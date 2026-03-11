<?php

namespace App\Filament\Resources\LaporanInsidens\Pages;

use App\Filament\Resources\LaporanInsidens\LaporanInsidenResource;
use App\Filament\Resources\LaporanInsidens\Schemas\LaporanInsidenInfolistSchema;
use App\Models\LaporanInsiden;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ViewLaporanInsiden extends ViewRecord
{
    protected static string $resource = LaporanInsidenResource::class;

    protected string $view = 'filament.resources.laporan-insidens.pages.view-laporan-insiden';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submit_laporan')
                ->label('Kirim Laporan')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->visible(
                    fn() =>
                    auth()->user()?->can('Submit:LaporanInsiden') &&
                        in_array($this->record->status, [LaporanInsiden::STATUS_DRAFT, LaporanInsiden::STATUS_REVISI])
                )
                ->requiresConfirmation()
                ->modalHeading('Kirim Laporan Insiden?')
                ->modalDescription('Laporan akan dikirim ke kepala unit untuk diverifikasi. Pastikan semua data sudah lengkap.')
                ->action(function () {
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

                    $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
                }),

            Action::make('verifikasi_laporan')
                ->label('Verifikasi Laporan')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(
                    fn() =>
                    auth()->user()?->can('Verifikasi:LaporanInsiden') &&
                        $this->record->status === LaporanInsiden::STATUS_DILAPORKAN
                )
                ->requiresConfirmation()
                ->modalHeading('Verifikasi Laporan Insiden?')
                ->modalDescription('Laporan akan diverifikasi dan diteruskan ke tim mutu.')
                ->action(function () {
                    $this->record->verifikasiLaporan(auth()->id());

                    $notifyUsers = User::role(['tim_mutu', 'admin'])->get();
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

                    $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
                }),

            Action::make('kembalikan_laporan')
                ->label('Kembalikan ke Pelapor')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->visible(
                    fn() =>
                    auth()->user()?->can('Kembalikan:LaporanInsiden') &&
                        $this->record->status === LaporanInsiden::STATUS_DILAPORKAN
                )
                ->schema([
                    Textarea::make('rejection_reason')
                        ->label('Alasan Pengembalian')
                        ->placeholder('Jelaskan apa yang perlu diperbaiki oleh pelapor...')
                        ->required()
                        ->minLength(10)
                        ->rows(4),
                ])
                ->modalHeading('Kembalikan Laporan ke Pelapor')
                ->modalSubmitActionLabel('Kembalikan')
                ->action(function (array $data) {
                    $this->record->kembalikanKePelapor(auth()->id(), $data['rejection_reason']);

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

                    $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
                }),

            EditAction::make()
                ->label('Edit Laporan')
                ->icon('heroicon-o-pencil-square')
                ->visible(fn() => Auth::user()?->can('ViewAllData:LaporanInsiden') ||
                    (Auth::id() === $this->record->user_id &&
                        in_array($this->record->status, [LaporanInsiden::STATUS_DRAFT, LaporanInsiden::STATUS_REVISI, LaporanInsiden::STATUS_INVESTIGASI])))

        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components(LaporanInsidenInfolistSchema::sections())->columns(1);
    }

    public function getWorkflowSteps(): array
    {
        return [
            [
                'key' => 'draft',
                'title' => 'Draft',
                'desc' => 'Tahap awal pembuatan laporan',
                'icon' => 'heroicon-o-pencil',
                'message' => 'Pelapor dapat mengedit seluruh field sebelum laporan dikirim.',
                'by_key' => null,
                'date_key' => null,
            ],
            [
                'key' => 'dilaporkan',
                'title' => 'Dilaporkan',
                'desc' => 'Laporan dikirim ke kepala unit',
                'icon' => 'heroicon-o-paper-airplane',
                'message' => 'Menunggu proses verifikasi dari kepala unit.',
                'by_key' => 'reported_by',
                'date_key' => 'reported_at',
            ],
            [
                'key' => 'diverifikasi',
                'title' => 'Verifikasi',
                'desc' => 'Evaluasi kepala unit',
                'icon' => 'heroicon-o-shield-check',
                'message' => 'Grading risiko ditentukan pada tahap ini.',
                'by_key' => 'verified_by',
                'date_key' => 'verified_at',
            ],
            [
                'key' => 'investigasi',
                'title' => 'Investigasi',
                'desc' => 'Investigasi oleh tim mutu',
                'icon' => 'heroicon-o-magnifying-glass',
                'message' => 'Menentukan akar penyebab dan rekomendasi perbaikan.',
                'by_key' => null,
                'date_key' => null,
            ],
        ];
    }

    public function getStepDetail(array $step): string
    {
        $detail = $step['message'];

        // For draft step, show the reporter/creator
        if ($step['key'] === 'draft' && $this->record->user) {
            $userName = $this->record->user->name;
            $dateFormatted = $this->record->created_at->format('d F Y H:i');
            $detail .= "\n\n👤 Pelapor: {$userName}\n⏰ Tanggal: {$dateFormatted}";
        } elseif ($step['by_key'] && $step['date_key']) {
            $byId = $this->record->{$step['by_key']};
            $date = $this->record->{$step['date_key']};

            if ($byId && $date) {
                $user = User::find($byId);
                $userName = $user ? $user->name : 'Tidak diketahui';
                $dateFormatted = $date->format('d F Y H:i');

                $detail .= "\n\n👤 Oleh: {$userName}\n⏰ Tanggal: {$dateFormatted}";
            }
        }

        return $detail;
    }

    public function getStepStatus(string $stepKey, string $status): string
    {
        $order = [
            'draft',
            'dilaporkan',
            'diverifikasi',
            'investigasi'
        ];

        $currentIndex = array_search($status, $order);
        $stepIndex = array_search($stepKey, $order);

        if ($stepIndex < $currentIndex) {
            return 'done';
        }
        if ($stepIndex == $currentIndex) {
            return 'current';
        }
        return 'pending';
    }
}
