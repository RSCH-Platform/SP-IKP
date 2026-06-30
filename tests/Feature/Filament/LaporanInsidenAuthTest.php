<?php

use App\Models\LaporanInsiden;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\Resources\LaporanInsidens\Pages\EditLaporanInsiden;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permissions = [
        'ViewAny:LaporanInsiden',
        'View:LaporanInsiden',
        'Create:LaporanInsiden',
        'Update:LaporanInsiden',
        'Delete:LaporanInsiden',
        'Submit:LaporanInsiden',
        'ViewAllData:LaporanInsiden',
        'ForceEdit:LaporanInsiden',
    ];
    foreach ($permissions as $permission) {
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
    }

    $admin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
    $timMutu = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'tim_mutu']);
    $user = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'user']);
    
    $timMutu->givePermissionTo($permissions); // give all to timMutu
    $user->givePermissionTo(['ViewAny:LaporanInsiden', 'View:LaporanInsiden', 'Submit:LaporanInsiden', 'Create:LaporanInsiden', 'Update:LaporanInsiden']);
});

it('denies access to edit page for unauthorized users', function () {
    // Buat user biasa (pelapor)
    $userLain = User::factory()->create();
    $userLain->assignRole('user');

    $userPemilik = User::factory()->create();
    $userPemilik->assignRole('user');

    // Buat laporan milik $userPemilik
    $laporan = LaporanInsiden::factory()->create([
        'user_id' => $userPemilik->id,
        'status' => 'draft'
    ]);

    // $userLain mencoba akses laporan milik orang lain
    $this->actingAs($userLain);
    
    // Filament resources biasanya diakses via rute tertentu atau bisa dites vis Livewire
    // Jika kita panggil Livewire test untuk Edit page
    Livewire::test(EditLaporanInsiden::class, [
        'record' => $laporan->getRouteKey(),
    ]);
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

it('allows access to edit page for the owner', function () {
    $userPemilik = User::factory()->create();
    $userPemilik->assignRole('user');

    $laporan = LaporanInsiden::factory()->create([
        'user_id' => $userPemilik->id,
        'status' => 'draft'
    ]);

    $this->actingAs($userPemilik);

    Livewire::test(EditLaporanInsiden::class, [
        'record' => $laporan->getRouteKey(),
    ])->assertSuccessful();
});

it('allows tim mutu to access all reports', function () {
    $timMutu = User::factory()->create();
    $timMutu->assignRole('tim_mutu');

    $userLain = User::factory()->create();

    $laporan = LaporanInsiden::factory()->create([
        'user_id' => $userLain->id,
        'status' => 'dilaporkan'
    ]);

    $this->actingAs($timMutu);

    Livewire::test(EditLaporanInsiden::class, [
        'record' => $laporan->getRouteKey(),
    ])->assertSuccessful();
});
