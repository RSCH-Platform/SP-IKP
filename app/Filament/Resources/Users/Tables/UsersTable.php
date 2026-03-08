<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use STS\FilamentImpersonate\Actions\Impersonate;

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
                TrashedFilter::make(),

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

                Impersonate::make(),

                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->visible(fn($record) => $record->deleted_at === null),

                ActionGroup::make([

                    Action::make('assign_role')
                        ->label('Assign Role')
                        ->icon('heroicon-m-shield-check')
                        ->color('info')
                        ->visible(fn($record) => $record->deleted_at === null)
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
                        }),

                    Action::make('set_nip')
                        ->label('Set NIP')
                        ->icon('heroicon-m-identification')
                        ->color('warning')
                        ->visible(fn($record) => $record->deleted_at === null)
                        ->form([
                            TextInput::make('nip')
                                ->label('NIP')
                                ->required()
                                ->maxLength(50)
                                ->default(fn($record) => $record->nip)
                                ->placeholder('Contoh: 0000.00000'),
                        ])
                        ->action(function (array $data, $record) {

                            $record->update([
                                'nip' => $data['nip'],
                            ]);

                            Notification::make()
                                ->title('NIP berhasil disimpan')
                                ->success()
                                ->send();
                        }),

                    Action::make('reset_password')
                        ->label('Reset Password')
                        ->icon('heroicon-m-key')
                        ->color('gray')
                        ->visible(fn($record) => $record->deleted_at === null)
                        ->requiresConfirmation()
                        ->schema([
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

                            $record->update([
                                'password' => bcrypt($data['password']),
                            ]);

                            Notification::make()
                                ->title('Password berhasil direset')
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->label('Soft Delete')
                        ->visible(fn($record) => $record->deleted_at === null),

                    RestoreAction::make()
                        ->label('Restore')
                        ->visible(fn($record) => $record->deleted_at !== null),

                    ForceDeleteAction::make()
                        ->label('Hard Delete')
                        ->color('danger')
                        ->visible(fn($record) => $record->deleted_at !== null),

                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-cog-6-tooth'),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
