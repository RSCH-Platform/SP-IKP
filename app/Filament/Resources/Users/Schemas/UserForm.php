<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $isEdit = $schema->getLivewire() instanceof \Filament\Resources\Pages\EditRecord;

        return $schema
            ->columns(2)
            ->components([
                Section::make('Informasi Pengguna')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('no_hp')
                            ->label('No HP')
                            ->required()
                            ->tel()
                            ->length(12)
                            ->regex('/^08\d{10}$/')
                            ->validationMessages([
                                'regex'  => 'No HP harus diawali 08 dan terdiri dari 12 digit angka.',
                                'length' => 'No HP harus terdiri dari 12 digit angka.',
                            ])
                            ->unique(User::class, 'no_hp', ignoreRecord: true)
                            ->columnSpan(1),

                        TextInput::make('nip')
                            ->label('NIP')
                            ->nullable()
                            ->maxLength(50)
                            ->placeholder('Kosongkan jika belum diverifikasi')
                            ->unique(User::class, 'nip', ignoreRecord: true)
                            ->helperText('Isi NIP untuk menandai user sebagai terverifikasi.')
                            ->columnSpan(2),
                    ]),

                Section::make('Role')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('roles')
                            ->label('Role')
                            ->relationship('roles', 'name')
                            ->options(Role::orderBy('name')->pluck('name', 'id'))
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ]),

                Section::make('Password')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->required(fn() => ! $isEdit)
                            ->dehydrated(fn(?string $state) => filled($state))
                            ->helperText($isEdit ? 'Kosongkan jika tidak ingin mengubah password.' : null)
                            ->columnSpan(1),

                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->revealable()
                            ->same('password')
                            ->required(fn() => ! $isEdit)
                            ->dehydrated(false)
                            ->columnSpan(1),
                    ])->columns(2),
            ]);
    }
}
