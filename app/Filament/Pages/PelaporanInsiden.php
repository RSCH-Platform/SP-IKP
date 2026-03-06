<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LaporanInsidens\Schemas\LaporanInsidenFormSchema;
use App\Models\LaporanInsiden;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
                ...LaporanInsidenFormSchema::sections(withAdminFields: false),
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
        $data['user_id'] = Auth::id();
        $data['status'] = 'submitted';

        LaporanInsiden::create($data);

        Notification::make()
            ->title('Laporan berhasil disubmit')
            ->body('Laporan insiden Anda telah berhasil dikirim untuk direview.')
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
