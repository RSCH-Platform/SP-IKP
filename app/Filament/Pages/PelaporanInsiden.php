<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LaporanInsidens\LaporanInsidenResource;
use App\Filament\Resources\LaporanInsidens\Schemas\LaporanInsidenFormSchema;
use App\Models\LaporanInsiden;
use App\Models\TimelineCategory;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PelaporanInsiden extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use HasPageShield;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.pelaporan-insiden';

    protected static ?string $navigationLabel = 'Pelaporan Insiden';

    protected static ?string $title = null;

    public ?array $data = [];

    public function mount(): void
    {
        /** @var User|null $authUser */
        $authUser = Auth::user();

        $defaults = [
            'user_id'        => $authUser?->id,
            'unit_kerja_id'  => $authUser?->unitKerjas()->first()?->id,
            'nama_pelapor'   => $authUser?->name,
            'unit_kerja'     => $authUser?->unitKerjas->first()->unit_name ?? 'Unit Kerja Tidak Ditemukan',
            'tanggal_lapor'  => now()->format('Y-m-d'),
            'tanggal_insiden' => now()->format('Y-m-d'),
            'status'         => 'draft',
            'nomor_telepon'           => $authUser?->no_hp,
        ];

        // if (app()->environment('local', 'dev')) {
        //     $defaults = array_merge($defaults, [
        //         'waktu_insiden'           => now()->format('H:i'),
        //         'jenis_insiden'           => 'KPC (Kondisi Potensial Cedera)',
        //         'lokasi_insiden'          => 'Ruang IGD Lantai 1',
        //         'nama_pasien'             => 'Budi Santoso (Dev)',
        //         'nomor_rekam_medis'       => 'RM-DEV-001',
        //         'ruangan'                 => 'Ruang Anggrek',
        //         'umur'                    => 45,
        //         'kelompok_umur'           => '>30 tahun - 65 tahun',
        //         'jenis_kelamin'           => 'Laki-laki',
        //         'penanggung_biaya'        => 'BPJS',
        //         'tanggal_masuk_rs'        => now()->format('Y-m-d H:i'),
        //         'insiden_terjadi_pada'    => 'Pasien',
        //         'kategori_insiden'        => 'Pasien Jatuh',
        //         'dampak_insiden'          => 'Tidak ada cedera',
        //         'deskripsi_kategori_insiden' => '[DEV] Insiden pasien jatuh di kamar mandi disebabkan oleh lantai yang licin dan tidak adanya pegangan. Faktor risiko pasien meliputi usia lanjut dan penggunaan obat antihipertensi.',
        //         'tindakan_dilakukan'      => "[DEV] 1. Memberikan pertolongan pertama kepada pasien\n2. Menghubungi dokter jaga\n3. Melaporkan kepada kepala ruangan\n4. Mengisi formulir laporan insiden\n5. Memasang tanda lantai licin di kamar mandi",
        //     ]);
        // }

        $this->form->fill($defaults);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->model(LaporanInsiden::class)
            ->schema([
                Wizard::make(LaporanInsidenFormSchema::steps(withAdminFields: false))->columnSpanFull(),
                Forms\Components\Hidden::make('status')->default('draft'),
                Forms\Components\Hidden::make('user_id')->default(Auth::id()),
            ])
            ->statePath('data');
    }

    public function simpanDraft(): void
    {
        $data = $this->form->getState();
        $data['user_id'] = Auth::id();
        $data['status'] = 'draft';

        $laporan = $this->createLaporanWithTimeline($data);

        Notification::make()
            ->title('Draft berhasil disimpan')
            ->success()
            ->send();

        $this->redirect(LaporanInsidenResource::getUrl('edit', ['record' => $laporan->id]));
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        if (! $this->hasValidTimeline($data['timelineEvents'] ?? [])) {
            Notification::make()
                ->title('Tidak dapat mengirim laporan')
                ->body('Kronologi timeline belum lengkap. Lengkapi data kronologi sebelum mengirim laporan ke kepala unit.')
                ->warning()
                ->send();

            return;
        }

        $data['user_id']     = Auth::id();
        $data['status']      = LaporanInsiden::STATUS_DILAPORKAN;
        $data['reported_at'] = now();

        $laporan = $this->createLaporanWithTimeline($data);

        // Notify kepala_unit users about the new report
        $kepalaUnits = User::role('kepala_unit')->get();
        if ($kepalaUnits->isNotEmpty()) {
            Notification::make()
                ->title('Laporan Insiden Baru')
                ->body("Ada laporan insiden baru dari {$laporan->nama_pelapor} yang perlu diverifikasi.")
                ->warning()
                ->sendToDatabase($kepalaUnits);
        }

        Notification::make()
            ->title('Laporan berhasil dikirim')
            ->body('Laporan insiden Anda telah berhasil dikirim untuk diverifikasi oleh kepala unit.')
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('simpanDraft')
                ->label('Simpan Draft')
                ->color('success')
                ->action('simpanDraft'),

            // \Filament\Actions\Action::make('submit')
            //     ->label('Submit Laporan')
            //     ->color('primary')
            //     ->action('submit'),
        ];
    }

    private function createLaporanWithTimeline(array $data): LaporanInsiden
    {
        $timelineEvents = $data['timelineEvents'] ?? [];
        unset($data['timelineEvents']);

        $laporan = LaporanInsiden::create($data);

        if (! empty($timelineEvents)) {
            $this->saveTimeline($laporan, $timelineEvents);
        }

        return $laporan;
    }

    private function saveTimeline(LaporanInsiden $laporan, array $timelineEvents): void
    {
        $categoryMap = TimelineCategory::all()->keyBy('code');

        foreach ($timelineEvents as $event) {
            $timelineEvent = $laporan->timelineEvents()->create([
                'event_datetime' => $event['event_datetime'] ?? now(),
                'created_by' => $laporan->user_id,
            ]);

            $entries = collect($event['entries'] ?? [])
                ->map(function (array $entry) use ($categoryMap) {
                    $entry['category_id'] = $entry['category_id'] ?? $categoryMap[$entry['category_code']]?->id;

                    return $entry;
                })
                // Keep only entries that have a valid category_id
                ->filter(fn($entry) => ! empty($entry['category_id']))
                // Normalize duplicates by keeping the last submitted value per category
                ->unique('category_id')
                ->values();

            foreach ($entries as $entry) {
                $timelineEvent->entries()->updateOrCreate(
                    ['category_id' => $entry['category_id']],
                    [
                        'description' => $entry['description'] ?? '',
                        'created_by' => $laporan->user_id,
                    ]
                );
            }
        }
    }

    private function hasValidTimeline(array $timelineEvents): bool
    {
        if (empty($timelineEvents)) {
            return false;
        }

        $categoryMap = TimelineCategory::all()->keyBy('code');

        foreach ($timelineEvents as $event) {
            foreach ($event['entries'] ?? [] as $entry) {
                $categoryId = $entry['category_id'] ?? $categoryMap[$entry['category_code']]?->id;

                if (! empty($categoryId)) {
                    return true;
                }
            }
        }

        return false;
    }
}
