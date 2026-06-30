<?php

use App\Filament\Resources\LaporanInsidens\LaporanInsidenResource;
use App\Filament\Resources\LaporanInsidens\Pages\CreateLaporanInsiden;
use App\Filament\Resources\LaporanInsidens\Pages\EditLaporanInsiden;
use App\Filament\Resources\LaporanInsidens\Pages\ListLaporanInsidens;
use App\Filament\Resources\LaporanInsidens\Pages\ViewLaporanInsiden;
use App\Models\LaporanInsiden;
use App\Models\User;
use App\Models\UnitKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    $admin->givePermissionTo($permissions);

    $user = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'user']);
    $user->givePermissionTo(['ViewAny:LaporanInsiden', 'View:LaporanInsiden', 'Submit:LaporanInsiden', 'Create:LaporanInsiden', 'Update:LaporanInsiden']);

    $this->user = User::factory()->create();
    $this->user->assignRole('user');
    
    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');
});

it('can render list page', function () {
    $this->actingAs($this->user);

    $this->get(LaporanInsidenResource::getUrl('index'))->assertSuccessful();
});

it('can render create page', function () {
    $this->actingAs($this->user);

    $this->get(LaporanInsidenResource::getUrl('create'))->assertSuccessful();
});

it('can render edit page', function () {
    $this->actingAs($this->admin);

    $record = LaporanInsiden::factory()->create();

    $this->get(LaporanInsidenResource::getUrl('edit', ['record' => $record]))->assertSuccessful();
});

it('can render view page', function () {
    $this->actingAs($this->user);

    $record = LaporanInsiden::factory()->create(['user_id' => $this->user->id]);

    $this->get(LaporanInsidenResource::getUrl('view', ['record' => $record]))->assertSuccessful();
});

it('can list laporan insidens on the table', function () {
    $this->actingAs($this->user);

    $records = LaporanInsiden::factory()->count(3)->create(['user_id' => $this->user->id]);

    Livewire::test(ListLaporanInsidens::class)
        ->assertCanSeeTableRecords($records)
        ->assertCountTableRecords(3);
});

it('can render table columns', function () {
    $this->actingAs($this->user);
    LaporanInsiden::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(ListLaporanInsidens::class)
        ->assertCanRenderTableColumn('nomor_laporan')
        ->assertCanRenderTableColumn('tanggal_insiden')
        ->assertCanRenderTableColumn('status');
});

it('can create a laporan insiden', function () {
    $this->actingAs($this->user);

    $unitKerja = UnitKerja::factory()->create();

    $newData = LaporanInsiden::factory()->make([
        'user_id' => $this->user->id,
        'unit_kerja_id' => $unitKerja->id,
        'jenis_insiden' => 'KNC (Kejadian Nyaris Cedera)',
        'dampak_insiden' => 'Tidak ada cedera',
    ]);

    Livewire::test(CreateLaporanInsiden::class)
        ->fillForm([
            'nama_pelapor' => $newData->nama_pelapor,
            'unit_kerja_id' => $newData->unit_kerja_id,
            'jenis_insiden' => $newData->jenis_insiden,
            'tanggal_insiden' => $newData->tanggal_insiden->toDateString(),
            'waktu_insiden' => $newData->waktu_insiden,
            'dampak_insiden' => $newData->dampak_insiden,
            'deskripsi_kategori_insiden' => 'Testing desc',
            'pelapor_insiden_pasien' => 'dokter',
            'insiden_menyangkut_pasien' => 'pasien_rawat_inap',
            'spesialisasi_pasien' => 'penyakit_dalam',
            'lokasi_insiden' => 'Ruang IGD',
            'kategori_insiden' => 'Medication / Cairan IV',
            'tindakan_dilakukan' => 'P3K',
            'tindakan_dilakukan_oleh' => 'Dokter',
            'kejadian_pernah_terjadi_sebelumnya' => 'Ya',
            'kejadian_pernah_terjadi_sebelumnya_deskripsi' => 'test',
            'grading_risiko' => 'Biru',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(LaporanInsiden::class, [
        'nama_pelapor' => $this->user->name,
        'jenis_insiden' => $newData->jenis_insiden,
    ]);
});

it('can save existing laporan insiden', function () {
    $this->actingAs($this->admin);

    $record = LaporanInsiden::factory()->create([
        'nama_pelapor' => 'Old Name',
        'status' => 'draft',
        'pelapor_insiden_pasien' => 'dokter',
        'insiden_menyangkut_pasien' => 'pasien_rawat_inap',
        'spesialisasi_pasien' => 'penyakit_dalam',
        'deskripsi_kategori_insiden' => 'Testing desc',
        'jenis_insiden' => 'KNC (Kejadian Nyaris Cedera)',
        'kategori_insiden' => 'Medication / Cairan IV',
        'dampak_insiden' => 'Tidak ada cedera',
        'tindakan_dilakukan' => 'P3K',
        'tindakan_dilakukan_oleh' => 'Dokter',
        'kejadian_pernah_terjadi_sebelumnya' => 'Ya',
        'kejadian_pernah_terjadi_sebelumnya_deskripsi' => 'test',
    ]);

    Livewire::test(EditLaporanInsiden::class, [
        'record' => $record->getRouteKey(),
    ])
        ->fillForm([
            'lokasi_insiden' => 'New Location Edit',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh())
        ->lokasi_insiden->toBe('New Location Edit');
});

it('can filter laporan insidens by status', function () {
    $this->actingAs($this->admin);

    $records = LaporanInsiden::factory()->count(2)->create(['status' => 'draft']);
    LaporanInsiden::factory()->count(3)->create(['status' => 'dilaporkan']);

    Livewire::test(ListLaporanInsidens::class)
        ->assertCountTableRecords(5)
        ->filterTable('status', 'draft')
        ->assertCanSeeTableRecords($records)
        ->assertCountTableRecords(2);
});
