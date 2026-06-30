<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permissions = [
        'ViewAny:User',
        'View:User',
        'Create:User',
        'Update:User',
        'Delete:User',
    ];
    foreach ($permissions as $permission) {
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
    }

    $admin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
    $admin->givePermissionTo($permissions);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');
});

it('can render list page', function () {
    $this->actingAs($this->admin);

    $this->get(UserResource::getUrl('index'))->assertSuccessful();
});

it('can render create page', function () {
    $this->actingAs($this->admin);

    $this->get(UserResource::getUrl('create'))->assertSuccessful();
});

it('can render edit page', function () {
    $this->actingAs($this->admin);

    $record = User::factory()->create();

    $this->get(UserResource::getUrl('edit', ['record' => $record]))->assertSuccessful();
});

it('can list users on the table', function () {
    $this->actingAs($this->admin);

    $records = User::factory()->count(3)->create();

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords($records)
        ->assertCountTableRecords(4); // 3 + 1 admin
});

it('can create a user', function () {
    $this->actingAs($this->admin);

    $newData = User::factory()->make();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => $newData->name,
            'email' => $newData->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'nip' => '9999',
            'no_hp' => '081234567890',
            'address' => 'Jl. Test',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(User::class, [
        'email' => $newData->email,
    ]);
});

it('can save existing user', function () {
    $this->actingAs($this->admin);

    $record = User::factory()->create([
        'name' => 'Old Name',
    ]);

    Livewire::test(EditUser::class, [
        'record' => $record->getRouteKey(),
    ])
        ->fillForm([
            'name' => 'New Awesome Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh())
        ->name->toBe('New Awesome Name');
});

it('can search users by name', function () {
    $this->actingAs($this->admin);

    $record1 = User::factory()->create(['name' => 'John Doe']);
    $record2 = User::factory()->create(['name' => 'Jane Smith']);

    Livewire::test(ListUsers::class)
        ->searchTable('John')
        ->assertCanSeeTableRecords([$record1])
        ->assertCanNotSeeTableRecords([$record2]);
});
