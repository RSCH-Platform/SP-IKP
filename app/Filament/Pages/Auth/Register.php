<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getNoHpFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function getNoHpFormComponent(): Component
    {
        return TextInput::make('no_hp')
            ->label('No HP')
            ->required()
            ->tel()
            ->length(12)
            ->regex('/^08\d{10}$/')
            ->validationMessages([
                'regex' => 'No HP harus diawali 08 dan terdiri dari 12 digit angka.',
                'length' => 'No HP harus terdiri dari 12 digit angka.',
            ])
            ->unique(User::class, 'no_hp')
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function handleRegistration(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'no_hp' => $data['no_hp'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole('pelapor_ikp');

        return $user;
    }
}
