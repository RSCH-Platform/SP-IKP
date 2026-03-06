<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->placeholder('—')
                    ->copyable(),

                TextColumn::make('no_hp')
                    ->label('No HP')
                    ->searchable()
                    ->copyable(),

                BadgeColumn::make('is_verified')
                    ->label('Status')
                    ->getStateUsing(fn($record) => $record->is_verified ? 'Terverifikasi' : 'Belum Verifikasi')
                    ->colors([
                        'success' => 'Terverifikasi',
                        'warning' => 'Belum Verifikasi',
                    ]),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(','),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('verified')
                    ->label('Status Verifikasi')
                    ->options([
                        'verified'   => 'Terverifikasi',
                        'unverified' => 'Belum Verifikasi',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'verified') {
                            $query->whereNotNull('nip');
                        } elseif ($data['value'] === 'unverified') {
                            $query->whereNull('nip');
                        }
                    }),

                SelectFilter::make('roles')
                    ->label('Role')
                    ->options(Role::orderBy('name')->pluck('name', 'name'))
                    ->query(
                        fn($query, array $data) => $data['value']
                            ? $query->role($data['value'])
                            : $query
                    ),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square'),

                Action::make('assign_role')
                    ->label('Assign Role')
                    ->icon('heroicon-m-shield-check')
                    ->color('info')
                    ->form([
                        Select::make('roles')
                            ->label('Role')
                            ->options(Role::orderBy('name')->pluck('name', 'name'))
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->default(fn($record) => $record->roles->pluck('name')->toArray()),
                    ])
                    ->action(function (array $data, $record) {
                        $record->syncRoles($data['roles']);
                        Notification::make()
                            ->title('Role berhasil diperbarui')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Assign Role Pengguna')
                    ->modalSubmitActionLabel('Simpan'),

                Action::make('set_nip')
                    ->label('Set NIP')
                    ->icon('heroicon-m-identification')
                    ->color('warning')
                    ->form([
                        TextInput::make('nip')
                            ->label('NIP')
                            ->required()
                            ->maxLength(50)
                            ->default(fn($record) => $record->nip)
                            ->placeholder('Contoh: 0000.00000'),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update(['nip' => $data['nip']]);
                        Notification::make()
                            ->title('NIP berhasil disimpan, user sekarang terverifikasi')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Set NIP / Verifikasi Pengguna')
                    ->modalSubmitActionLabel('Verifikasi'),

                Action::make('reset_password')
                    ->label('Reset Password')
                    ->icon('heroicon-m-key')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->form([
                        TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8),
                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->revealable()
                            ->same('password')
                            ->required(),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update(['password' => bcrypt($data['password'])]);
                        Notification::make()
                            ->title('Password berhasil direset')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Reset Password Pengguna')
                    ->modalSubmitActionLabel('Reset Password'),

                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
