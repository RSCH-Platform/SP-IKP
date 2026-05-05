<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use App\Models\UnitKerja;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;

class PelaporSection
{
    public static function make(): Section
    {
        return Section::make('BAGIAN A: DATA PELAPOR')
            ->description('Identitas dan informasi kontak pelapor insiden (Otomatis Terisi)')
            ->icon('heroicon-o-user-circle')
            ->schema([
                Grid::make(2)->schema([
                    Hidden::make('nama_pelapor')
                        ->dehydrateStateUsing(function ($state, callable $get) {
                            $user = User::find($get('user_id'));

                            return $user?->name;
                        }),

                    Hidden::make('unit_kerja')
                        ->dehydrateStateUsing(function ($state, callable $get) {
                            $unit = UnitKerja::find($get('unit_kerja_id'));

                            return $unit?->unit_name;
                        }),

                    Select::make('user_id')
                        ->label('Nama Lengkap Pelapor')
                        ->required()
                        ->live()
                        ->searchable()
                        ->preload()
                        ->relationship('user', 'name', fn($query) => $query->orderBy('name'))
                        ->default(fn(): ?int => Auth::id())
                        ->afterStateHydrated(function ($state, callable $set): void {
                            if (! static::shouldLockPelaporIdentityFields()) {
                                return;
                            }

                            $authUser = static::getAuthenticatedUser();
                            if (! $authUser instanceof User) {
                                return;
                            }

                            $set('user_id', $authUser->getKey());
                            $set('unit_kerja_id', $authUser->unitKerjas()->first()?->id);
                        })
                        ->afterStateUpdated(function ($state, callable $set): void {
                            $selectedUser = User::with('unitKerjas')->find($state);

                            if (! $selectedUser instanceof User) {
                                $set('unit_kerja_id', null);
                                return;
                            }

                            $set('unit_kerja_id', $selectedUser->unitKerjas()->first()?->id);
                        })
                        ->disabled(fn(): bool => static::shouldLockPelaporIdentityFields())
                        ->dehydrated()
                        ->prefixIcon('heroicon-m-user')
                        ->placeholder('Pilih Pelapor'),

                    Forms\Components\Select::make('unit_kerja_id')
                        ->label('Unit Kerja / Departemen')
                        ->relationship('unitKerjas', 'unit_name', fn($query) => $query->orderBy('unit_name'))
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->required()
                        ->prefixIcon('heroicon-m-building-office')
                        ->helperText('Unit kerja pelapor')
                        ->placeholder('Pilih unit kerja')
                        ->default(function (): ?int {
                            return static::getAuthenticatedUser()?->unitKerjas()->first()?->id;
                        })
                        ->afterStateHydrated(function ($state, callable $set): void {
                            if (! static::shouldLockPelaporIdentityFields() || filled($state)) {
                                return;
                            }

                            $set('unit_kerja_id', static::getAuthenticatedUser()?->unitKerjas()->first()?->id);
                        })
                        ->disabled(fn(): bool => static::shouldLockPelaporIdentityFields())
                        ->dehydrated(),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('nomor_telepon')
                        ->label('Nomor Telepon / HP')
                        ->tel()
                        ->prefixIcon('heroicon-m-phone')
                        ->placeholder('08xx-xxxx-xxxx'),

                    Forms\Components\DatePicker::make('tanggal_lapor')
                        ->label('Tanggal Pelaporan')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->prefixIcon('heroicon-m-calendar')
                        ->displayFormat('d F Y'),
                ]),
            ])
            ->collapsible()
            ->collapsed()
            ->persistCollapsed()
            ->compact();
    }

    protected static function getAuthenticatedUser(): ?User
    {
        $authUser = Auth::user();

        return $authUser instanceof User ? $authUser : null;
    }

    protected static function shouldLockPelaporIdentityFields(): bool
    {
        $authUser = static::getAuthenticatedUser();

        if (! $authUser instanceof User) {
            return false;
        }

        return ! $authUser->can('ViewAllData:LaporanInsiden');
    }
}
