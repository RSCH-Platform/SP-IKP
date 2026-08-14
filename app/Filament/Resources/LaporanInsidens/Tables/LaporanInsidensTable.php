<?php

namespace App\Filament\Resources\LaporanInsidens\Tables;

use App\Filament\Resources\LaporanInsidens\LaporanInsidenResource;
use App\Models\LaporanInsiden;
use App\Models\UnitKerja;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LaporanInsidensTable
{
    public static function configure(Table $table): Table
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

        return $table
            ->columns([
                TextColumn::make('nomor_laporan')
                    ->label('No. Laporan')
                    ->icon('heroicon-m-document-text')
                    ->weight('bold')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('tanggal_insiden')
                    ->label('Tanggal Insiden')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('deskripsi_kategori_insiden')
                    ->label('Deskripsi Insiden')
                    ->searchable()
                    ->limit(60)
                    ->toggleable(),

                TextColumn::make('unit_kerja')
                    ->label('Unit Kerja')
                    ->formatStateUsing(fn($state, $record) => $state ?: ($record->unitKerja?->unit_name ?? '-'))
                    ->searchable()
                    ->badge()
                    ->toggleable(),

                TextColumn::make('jenis_insiden')
                    ->label('Jenis')
                    ->badge()
                    ->searchable()
                    ->toggleable()
                    ->color(fn(string $state): string => match ($state) {
                        'KPC (Kondisi Potensial Cedera)' => 'gray',
                        'KNC (Kejadian Nyaris Cedera)' => 'warning',
                        'KTC (Kejadian Tidak Cedera)' => 'info',
                        'KTD (Kejadian Tidak Diharapkan)' => 'danger',
                        'Sentinel' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('kategori_insiden')
                    ->label('Kategori')
                    ->searchable()
                    ->limit(35)
                    ->toggleable(),

                TextColumn::make('lokasi_insiden')
                    ->label('Lokasi')
                    ->searchable()
                    ->limit(35)
                    ->toggleable(),

                TextColumn::make('nama_pelapor')
                    ->label('Pelapor')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('nomor_telepon')
                    ->label('No. Telepon')
                    ->copyable()
                    ->copyMessage('Nomor telepon berhasil disalin!')
                    ->copyMessageDuration(1500)
                    ->toggleable(),

                TextColumn::make('dampak_insiden')
                    ->label('Dampak')
                    ->badge()
                    ->toggleable()
                    ->colors([
                        'success' => 'Tidak ada cedera',
                        'warning' => ['Cedera ringan', 'Cedera sedang'],
                        'danger' => ['Cedera berat', 'Meninggal'],
                    ]),


                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->toggleable()
                    ->color(fn(string $state): string => match ($state) {
                        'draft'         => 'gray',
                        'dilaporkan'    => 'warning',
                        'revisi'        => 'danger',
                        'diverifikasi'  => 'info',
                        'revisi_unit'   => 'danger',
                        'investigasi'   => 'success',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft'         => 'Draft',
                        'dilaporkan'    => 'Dilaporkan',
                        'revisi'        => 'Perlu Revisi',
                        'diverifikasi'  => 'Diverifikasi',
                        'revisi_unit'   => 'Perlu Revisi (Unit)',
                        'investigasi'   => 'Investigasi',
                        default         => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'draft'        => 'Draft',
                        'dilaporkan'   => 'Dilaporkan',
                        'revisi'       => 'Perlu Revisi',
                        'diverifikasi' => 'Diverifikasi',
                        'revisi_unit'  => 'Perlu Revisi (Unit)',
                        'investigasi'  => 'Investigasi',
                    ]),

                SelectFilter::make('jenis_insiden')
                    ->label('Jenis Insiden')
                    ->multiple()
                    ->options([
                        'KPC (Kondisi Potensial Cedera)' => 'KPC',
                        'KNC (Kejadian Nyaris Cedera)' => 'KNC',
                        'KTD (Kejadian Tidak Diharapkan)' => 'KTD',
                        'KTC (Kejadian Tidak Cedera)' => 'KTC',
                        'Sentinel' => 'Sentinel',
                    ]),

                SelectFilter::make('dampak_insiden')
                    ->label('Dampak')
                    ->multiple()
                    ->options([
                        'Tidak ada cedera' => 'Tidak ada cedera',
                        'Cedera ringan' => 'Cedera ringan',
                        'Cedera sedang' => 'Cedera sedang',
                        'Cedera berat' => 'Cedera berat',
                        'Meninggal' => 'Meninggal',
                    ]),

                SelectFilter::make('unit_kerja_id')
                    ->label('Unit Kerja')
                    ->multiple()
                    ->searchable()
                    ->options(
                        fn () => UnitKerja::query()
                            ->orderBy('unit_name')
                            ->pluck('unit_name', 'id')
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['values'])) {
                            return $query;
                        }

                        return $query->whereIn('unit_kerja_id', $data['values']);
                    }),

                TrashedFilter::make(),
            ])
            ->recordActions([
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
                    ->action(function ($record) use ($requiredFieldsForSubmit) {
                        $missingFields = collect($requiredFieldsForSubmit)
                            ->filter(fn($label, $field) => blank(data_get($record, $field)))
                            ->values()
                            ->all();

                        if ($record->timelineEvents()->count() === 0) {
                            $missingFields[] = 'Kronologi (Timeline)';
                        }

                        if (! empty($missingFields)) {
                            Notification::make()
                                ->title('Laporan belum bisa dikirim')
                                ->body('Lengkapi field wajib berikut: ' . implode(', ', $missingFields))
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->submitLaporan();

                        $kepalaUnits = User::role('kepala_unit')->get();

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

                /*
                |--------------------------------------------------------------------------
                | Workflow Kepala Unit
                |--------------------------------------------------------------------------
                */

                // ActionGroup::make([

                //     Action::make('verifikasi_laporan')
                //         ->label('Verifikasi')
                //         ->icon('heroicon-o-check-circle')
                //         ->color('success')
                //         ->visible(
                //             fn($record) =>
                //             auth()->user()?->can('Verifikasi:LaporanInsiden') &&
                //                 $record->status === LaporanInsiden::STATUS_DILAPORKAN
                //         )
                //         ->requiresConfirmation()
                //         ->modalHeading('Verifikasi Laporan')
                //         ->schema([
                //             ToggleButtons::make('grading_risiko')
                //                 ->label('Grading Risiko')
                //                 ->required()
                //                 ->options([
                //                     'Biru'   => '🔵 Biru (Tidak signifikan)',
                //                     'Hijau'  => '🟢 Hijau (Minor)',
                //                     'Kuning' => '🟡 Kuning (Moderat)',
                //                     'Merah'  => '🔴 Merah (Mayor)',
                //                     'Hitam'  => '⚫ Hitam (Katastropik)',
                //                 ])
                //                 ->colors([
                //                     'Biru'   => 'info',
                //                     'Hijau'  => 'success',
                //                     'Kuning' => 'warning',
                //                     'Merah'  => 'danger',
                //                     'Hitam'  => 'gray',
                //                 ])
                //                 ->inline()
                //                 ->helperText('Hanya diisi oleh Validator / Tim IKP')
                //                 ->default(fn($record) => $record->grading_risiko),
                //             Textarea::make('catatan_tambahan')
                //                 ->label('Catatan Verifikasi')
                //                 ->hidden()
                //                 ->rows(3)
                //                 ->default(fn($record) => $record->catatan_tambahan),
                //         ])
                //         ->action(function ($record, array $data) {
                //             $record->update([
                //                 'grading_risiko' => $data['grading_risiko'],
                //                 'catatan_tambahan' => $data['catatan_tambahan'],
                //             ]);
                //             $record->verifikasiLaporan(auth()->id());

                //             Notification::make()
                //                 ->title('Laporan diverifikasi')
                //                 ->success()
                //                 ->send();
                //         }),

                //     Action::make('kembalikan_ke_pelapor')
                //         ->label('Kembalikan ke Pelapor')
                //         ->icon('heroicon-o-arrow-uturn-left')
                //         ->color('danger')
                //         ->visible(
                //             fn($record) =>
                //             auth()->user()?->can('Kembalikan:LaporanInsiden') &&
                //                 $record->status === LaporanInsiden::STATUS_DILAPORKAN
                //         )
                //         ->schema([
                //             Textarea::make('rejection_reason')
                //                 ->label('Alasan Pengembalian')
                //                 ->required(),
                //         ])
                //         ->action(function ($record, array $data) {

                //             $record->kembalikanKePelapor(auth()->id(), $data['rejection_reason']);

                //             Notification::make()
                //                 ->title('Laporan dikembalikan ke pelapor')
                //                 ->danger()
                //                 ->send();
                //         }),

                // ])
                //     ->visible(fn() => auth()->user()?->can('Verifikasi:LaporanInsiden'))
                //     ->label('Verifikasi Laporan')
                //     ->button()
                //     ->color('info')
                //     ->icon('heroicon-o-building-office'),

                Action::make('verifikasi_laporan')
                    ->label('Verifikasi Laporan')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->button()
                    ->visible(
                        fn($record) =>
                        auth()->user()?->can('Verifikasi:LaporanInsiden') &&
                            $record->status === LaporanInsiden::STATUS_DILAPORKAN
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Laporan')
                    ->modalWidth('4xl')
                    ->schema([
                        Select::make('severity_score')
                            ->label('Penilaian Dampak (Severity)')
                            ->options([
                                1 => '1 — Tidak signifikan (Tidak ada cidera)',
                                2 => '2 — Minor (Cidera ringan / dapat diatasi dengan pertolongan pertama)',
                                3 => '3 — Moderat (Cedera sedang / gangguan fungsi reversibel / memperpanjang perawatan)',
                                4 => '4 — Mayor (Cedera luas / kehilangan fungsi irreversibel)',
                                5 => '5 — Katastropik (Kematian yang tidak berhubungan dengan perjalanan penyakit)',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($set, $get) {
                                $severity = (int)$get('severity_score');
                                $probability = (int)$get('probability_score');
                                if ($severity && $probability) {
                                    $result = \App\Services\RiskGradingEngine::calculate($severity, $probability);
                                    $set('grading_risiko', $result['risk_band']);
                                } else {
                                    $set('grading_risiko', null);
                                }
                            }),

                        Select::make('probability_score')
                            ->label('Penilaian Probabilitas (Probability)')
                            ->options([
                                1 => '1 — Sangat jarang / Rare (> 5 tahun/kali)',
                                2 => '2 — Jarang / Unlikely (> 2–5 tahun/kali)',
                                3 => '3 — Mungkin / Possible (1–2 tahun/kali)',
                                4 => '4 — Sering / Likely (Beberapa kali/tahun)',
                                5 => '5 — Sangat sering / Almost Certain (Tiap minggu/bulan)',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($set, $get) {
                                $severity = (int)$get('severity_score');
                                $probability = (int)$get('probability_score');
                                if ($severity && $probability) {
                                    $result = \App\Services\RiskGradingEngine::calculate($severity, $probability);
                                    $set('grading_risiko', $result['risk_band']);
                                } else {
                                    $set('grading_risiko', null);
                                }
                            }),

                        \Filament\Forms\Components\Placeholder::make('hasil_grading')
                            ->label('Hasil Grading')
                            ->content(function ($get) {
                                $severity = $get('severity_score');
                                $probability = $get('probability_score');

                                if (!$severity || !$probability) {
                                    return new \Illuminate\Support\HtmlString("
                                        <div class='p-4 border border-dashed rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-500 text-center italic'>
                                            Silakan lengkapi Penilaian Dampak dan Probabilitas terlebih dahulu.
                                        </div>
                                    ");
                                }

                                $result = \App\Services\RiskGradingEngine::calculate((int)$severity, (int)$probability);
                                
                                $theme = match($result['risk_band']) {
                                    'Merah' => [
                                        'bg' => 'bg-red-100 dark:bg-red-950/30',
                                        'border' => 'border-red-500 dark:border-red-600',
                                        'text' => 'text-red-900 dark:text-red-300',
                                        'icon' => '🔴',
                                    ],
                                    'Kuning' => [
                                        'bg' => 'bg-yellow-100 dark:bg-yellow-950/30',
                                        'border' => 'border-yellow-500 dark:border-yellow-600',
                                        'text' => 'text-yellow-900 dark:text-yellow-300',
                                        'icon' => '🟡',
                                    ],
                                    'Hijau' => [
                                        'bg' => 'bg-green-100 dark:bg-green-950/30',
                                        'border' => 'border-green-500 dark:border-green-600',
                                        'text' => 'text-green-900 dark:text-green-300',
                                        'icon' => '🟢',
                                    ],
                                    'Biru' => [
                                        'bg' => 'bg-blue-100 dark:bg-blue-950/30',
                                        'border' => 'border-blue-500 dark:border-blue-600',
                                        'text' => 'text-blue-900 dark:text-blue-300',
                                        'icon' => '🔵',
                                    ],
                                    default => [
                                        'bg' => 'bg-gray-100 dark:bg-gray-800',
                                        'border' => 'border-gray-500',
                                        'text' => 'text-gray-900 dark:text-gray-300',
                                        'icon' => '⚫',
                                    ],
                                };

                                return new \Illuminate\Support\HtmlString("
                                    <div class='p-5 border-2 rounded-xl {$theme['bg']} {$theme['border']} {$theme['text']} transition-all duration-300 ease-in-out shadow-sm'>
                                        <div class='flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4'>
                                            <div class='flex items-center gap-3'>
                                                <div class='text-4xl'>{$theme['icon']}</div>
                                                <div>
                                                    <div class='text-sm opacity-80 uppercase tracking-wider font-bold'>Risk Band</div>
                                                    <div class='text-2xl font-black'>{$result['risk_band']} ({$result['risk_level']})</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='mt-4 pt-4 border-t {$theme['border']} border-opacity-30'>
                                            <div class='font-bold uppercase tracking-wider text-xs mb-2 opacity-80'>Tindakan yang Diperlukan:</div>
                                            <div class='whitespace-pre-wrap font-medium leading-relaxed bg-white/50 dark:bg-black/30 p-4 rounded-lg border {$theme['border']} border-opacity-20'>{$result['required_action']}</div>
                                        </div>
                                    </div>
                                ");
                            }),
                            
                        \Filament\Forms\Components\Hidden::make('grading_risiko'),

                        Textarea::make('catatan_tambahan')
                            ->label('Catatan Verifikasi')
                            ->rows(3)
                            ->default(fn($record) => $record->catatan_tambahan),
                    ])
                    ->action(function ($record, array $data) {
                        $severity = (int) $data['severity_score'];
                        $probability = (int) $data['probability_score'];
                        $engineResult = \App\Services\RiskGradingEngine::calculate($severity, $probability);

                        // Simpan atau perbarui RiskAssessment
                        $record->riskAssessment()->updateOrCreate(
                            ['laporan_insiden_id' => $record->id],
                            [
                                'severity_score' => $engineResult['severity_score'],
                                'severity_level' => $engineResult['severity_level'],
                                'probability_score' => $engineResult['probability_score'],
                                'probability_level' => $engineResult['probability_level'],
                                'risk_score' => $engineResult['risk_score'],
                                'risk_level' => $engineResult['risk_level'],
                                'risk_band' => $engineResult['risk_band'],
                                'required_action' => $engineResult['required_action'],
                                'assessed_by' => auth()->id(),
                                'assessed_at' => now(),
                            ]
                        );

                        // Perbarui catatan dan kolom fallback
                        $record->update([
                            'grading_risiko' => $engineResult['risk_band'],
                            'catatan_tambahan' => $data['catatan_tambahan'] ?? $record->catatan_tambahan,
                        ]);
                        
                        $record->verifikasiLaporan(auth()->id());

                        Notification::make()
                            ->title('Laporan diverifikasi & dinilai')
                            ->success()
                            ->send();
                    }),

                /*
                |--------------------------------------------------------------------------
                | Workflow Tim Mutu
                |--------------------------------------------------------------------------
                */

                // ActionGroup::make([

                //     Action::make('mulai_investigasi')
                //         ->label('Mulai Investigasi')
                //         ->icon('heroicon-o-magnifying-glass')
                //         ->color('info')
                //         ->visible(
                //             fn($record) =>
                //             auth()->user()?->can('Investigasi:LaporanInsiden') &&
                //                 $record->status === LaporanInsiden::STATUS_DIVERIFIKASI
                //         )
                //         ->requiresConfirmation()
                //         ->action(function ($record) {
                //             if (blank($record->grading_risiko)) {
                //                 Notification::make()
                //                     ->title('Belum bisa investigasi')
                //                     ->body('Grading risiko wajib diisi saat verifikasi sebelum memulai investigasi.')
                //                     ->danger()
                //                     ->send();

                //                 return;
                //             }

                //             $record->mulaiInvestigasi(auth()->id());
                //         }),

                //     Action::make('kembalikan_ke_unit')
                //         ->label('Kembalikan ke Kepala Unit')
                //         ->icon('heroicon-o-arrow-uturn-left')
                //         ->color('danger')
                //         ->visible(
                //             fn($record) =>
                //             auth()->user()?->can('KembalikanUnit:LaporanInsiden') &&
                //                 $record->status === LaporanInsiden::STATUS_DIVERIFIKASI
                //         )
                //         ->schema([
                //             Textarea::make('rejection_reason')
                //                 ->label('Alasan Pengembalian')
                //                 ->required(),
                //         ])
                //         ->action(function ($record, array $data) {

                //             $record->kembalikanKeKepalaUnit(auth()->id(), $data['rejection_reason']);

                //             Notification::make()
                //                 ->title('Laporan dikembalikan ke kepala unit')
                //                 ->danger()
                //                 ->send();
                //         }),

                // ])
                //     ->visible(fn() => auth()->user()?->can('Investigasi:LaporanInsiden'))
                //     ->button()
                //     ->color('success')
                //     ->label('Investigasi Insiden')
                //     ->icon('heroicon-o-shield-check'),

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
                    ->action(function ($record) {
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

                        redirect(LaporanInsidenResource::getUrl('edit', ['record' => $record]));
                    })
                    ->button()
                    ->color('success')
                    ->label('Investigasi Insiden')
                    ->icon('heroicon-o-shield-check'),

                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(function ($record) {
                            $user = auth()->user();

                            // Force-edit permission bypasses workflow restrictions
                            if ($user?->can('ForceEdit:LaporanInsiden')) {
                                return true;
                            }

                            if (! $user?->can('Update:LaporanInsiden')) {
                                return false;
                            }

                            if (in_array($record->status, [
                                LaporanInsiden::STATUS_DRAFT,
                                LaporanInsiden::STATUS_REVISI,
                            ], true)) {
                                return $user->can('Submit:LaporanInsiden');
                            }

                            if ($record->status === LaporanInsiden::STATUS_DILAPORKAN) {
                                return $user->can('Verifikasi:LaporanInsiden')
                                    || $user->can('Kembalikan:LaporanInsiden');
                            }

                            if ($record->status === LaporanInsiden::STATUS_REVISI_UNIT) {
                                return $user->can('Verifikasi:LaporanInsiden');
                            }

                            if ($record->status === LaporanInsiden::STATUS_INVESTIGASI) {
                                return $user->can('Investigasi:LaporanInsiden');
                            }

                            return false;
                        }),
                ])
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->button()
                    ->label('Aksi')
            ])
            ->selectable()
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
