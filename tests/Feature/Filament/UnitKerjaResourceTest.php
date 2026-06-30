<?php

use App\Filament\Resources\UnitKerjas\Pages\CreateUnitKerja;
use App\Filament\Resources\UnitKerjas\Pages\EditUnitKerja;
use App\Filament\Resources\UnitKerjas\Pages\ListUnitKerjas;
use App\Filament\Resources\UnitKerjas\UnitKerjaResource;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permissions = [
        'ViewAny:UnitKerja',
        'View:UnitKerja',
        'Create:UnitKerja',
        'Update:UnitKerja',
        'Delete:UnitKerja',
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

    $this->get(UnitKerjaResource::getUrl('index'))->assertSuccessful();
});

it('can render create page', function () {
    $this->actingAs($this->admin);

    $this->get(UnitKerjaResource::getUrl('create'))->assertSuccessful();
});

it('can render edit page', function () {
    $this->actingAs($this->admin);

    $record = UnitKerja::factory()->create();

    $this->get(UnitKerjaResource::getUrl('edit', ['record' => $record]))->assertSuccessful();
});

it('can list unit kerjas on the table', function () {
    $this->actingAs($this->admin);

    $records = UnitKerja::factory()->count(3)->create();

    Livewire::test(ListUnitKerjas::class)
        ->assertCanSeeTableRecords($records)
        ->assertCountTableRecords(3);
});

it('can create a unit kerja', function () {
    $this->actingAs($this->admin);

    $newData = UnitKerja::factory()->make();

    Livewire::test(CreateUnitKerja::class)
        ->fillForm([
            'unit_name' => $newData->unit_name,
            'slug' => \Illuminate\Support\Str::slug($newData->unit_name),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(UnitKerja::class, [
        'unit_name' => $newData->unit_name,
    ]);
});

it('can save existing unit kerja', function () {
    $this->actingAs($this->admin);

    $record = UnitKerja::factory()->create([
        'unit_name' => 'Old Name',
    ]);

    Livewire::test(EditUnitKerja::class, [
        'record' => $record->getRouteKey(),
    ])
        ->fillForm([
            'unit_name' => 'New Awesome Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh())
        ->unit_name->toBe('New Awesome Name');
});

it('can search unit kerjas by name', function () {
    $this->actingAs($this->admin);

    $record1 = UnitKerja::factory()->create(['unit_name' => 'Ruang IGD']);
    $record2 = UnitKerja::factory()->create(['unit_name' => 'Farmasi']);

    Livewire::test(ListUnitKerjas::class)
        ->searchTable('IGD')
        ->assertCanSeeTableRecords([$record1])
        ->assertCanNotSeeTableRecords([$record2]);
});
