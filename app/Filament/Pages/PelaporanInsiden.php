<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LaporanInsidens\Schemas\LaporanInsidenFormSchema;
use App\Models\LaporanInsiden;
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

    protected static ?string $title = 'Form Pelaporan Insiden Keselamatan Pasien';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'nama_pelapor' => Auth::user()->name,
            'tanggal_lapor' => now()->format('Y-m-d'),
            'tanggal_insiden' => now()->format('Y-m-d'),
            'status' => 'draft',
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
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

        LaporanInsiden::create($data);

        Notification::make()
            ->title('Draft berhasil disimpan')
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $data['user_id']     = Auth::id();
        $data['status']      = LaporanInsiden::STATUS_DILAPORKAN;
        $data['reported_at'] = now();

        $laporan = LaporanInsiden::create($data);

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
                ->color('gray')
                ->action('simpanDraft'),

            \Filament\Actions\Action::make('submit')
                ->label('Submit Laporan')
                ->color('primary')
                ->action('submit'),
        ];
    }
}
