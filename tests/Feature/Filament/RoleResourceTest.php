<?php

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Roles\RoleResource;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permissions = [
        'ViewAny:Role',
        'View:Role',
        'Create:Role',
        'Update:Role',
        'Delete:Role',
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

    $this->get(RoleResource::getUrl('index'))->assertSuccessful();
});

it('can render create page', function () {
    $this->actingAs($this->admin);

    $this->get(RoleResource::getUrl('create'))->assertSuccessful();
});

it('can render edit page', function () {
    $this->actingAs($this->admin);

    $record = Role::firstOrCreate(['name' => 'test_role']);

    $this->get(RoleResource::getUrl('edit', ['record' => $record]))->assertSuccessful();
});

it('can list roles on the table', function () {
    $this->actingAs($this->admin);

    $records = [
        Role::firstOrCreate(['name' => 'test_role_1']),
        Role::firstOrCreate(['name' => 'test_role_2']),
    ];

    // the table contains super_admin and test_role_1 and test_role_2
    Livewire::test(ListRoles::class)
        ->assertCanSeeTableRecords($records)
        ->assertCountTableRecords(3);
});

it('can create a role', function () {
    $this->actingAs($this->admin);

    Livewire::test(CreateRole::class)
        ->fillForm([
            'name' => 'New Awesome Role',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('roles', [
        'name' => 'New Awesome Role',
    ]);
});

it('can save existing role', function () {
    $this->actingAs($this->admin);

    $record = Role::firstOrCreate(['name' => 'old_role']);

    Livewire::test(EditRole::class, [
        'record' => $record->id,
    ])
        ->fillForm([
            'name' => 'Updated Awesome Role',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh())
        ->name->toBe('Updated Awesome Role');
});

it('can search roles by name', function () {
    $this->actingAs($this->admin);

    $record1 = Role::firstOrCreate(['name' => 'Role A']);
    $record2 = Role::firstOrCreate(['name' => 'Role B']);

    Livewire::test(ListRoles::class)
        ->searchTable('Role A')
        ->assertCanSeeTableRecords([$record1])
        ->assertCanNotSeeTableRecords([$record2]);
});
