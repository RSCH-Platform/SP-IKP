<?php

use App\Models\LaporanInsiden;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\Resources\LaporanInsidens\Pages\EditLaporanInsiden;
use Livewire\Livewire;
use Illuminate\Support\Facades\Notification;

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
    $kepala = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'kepala_unit']);
    $timMutu = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'tim_mutu']);
    $user = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'user']);
    $timMutu->givePermissionTo($permissions);
    $kepala->givePermissionTo($permissions);
    $user->givePermissionTo(['ViewAny:LaporanInsiden', 'View:LaporanInsiden', 'Submit:LaporanInsiden', 'Create:LaporanInsiden', 'Update:LaporanInsiden']);
});

it('fails to submit if mandatory fields are missing', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    // Create a complete valid draft first
    $laporan = LaporanInsiden::factory()->create([
        'user_id' => $user->id,
        'status' => 'draft',
    ]);

    $this->actingAs($user);

    Livewire::test(EditLaporanInsiden::class, [
        'record' => $laporan->getRouteKey(),
    ])
    ->set('data.lokasi_insiden', null) // Remove mandatory field via UI state
    ->call('submitLaporan');
    
    $laporan->refresh();
    expect($laporan->status)->toBe('draft');
});

it('changes status to dilaporkan and sends notification when submitted correctly', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->assignRole('user');

    $kepalaUnit = User::factory()->create();
    $kepalaUnit->assignRole('kepala_unit');

    // Make sure we have a valid complete draft
    $laporan = LaporanInsiden::factory()->create([
        'user_id' => $user->id,
        'status' => 'draft',
        'nama_pelapor' => 'Test Name',
        'unit_kerja_id' => \App\Models\UnitKerja::factory()->create()->id,
        'tanggal_lapor' => now()->toDateString(),
        'jenis_insiden' => 'KNC (Kejadian Nyaris Cedera)',
        'tanggal_insiden' => now()->toDateString(),
        'waktu_insiden' => '10:00:00',
        'lokasi_insiden' => 'Ruang IGD',
        'insiden_menyangkut_pasien' => 'pasien_rawat_inap',
        'pelapor_insiden_pasien' => 'dokter',
        'spesialisasi_pasien' => 'penyakit_dalam',
        'insiden_terjadi_pada' => 'Pasien',
        'kategori_insiden' => 'Medication / Cairan IV',
        'deskripsi_kategori_insiden' => 'Testing desc',
        'dampak_insiden' => 'Tidak ada cedera',
        'tindakan_dilakukan' => 'P3K',
        'tindakan_dilakukan_oleh' => 'Dokter',
        'kejadian_pernah_terjadi_sebelumnya' => 'Ya',
        'kejadian_pernah_terjadi_sebelumnya_deskripsi' => 'test',
    ]);
    
    // Also attach a timeline event to satisfy validation
    $laporan->timelineEvents()->create([
        'title' => 'Kejadian',
        'event_datetime' => now(),
        'description' => 'Kronologi kejadian testing',
    ]);

    $this->actingAs($user);

    $livewire = Livewire::test(EditLaporanInsiden::class, [
        'record' => $laporan->getRouteKey(),
    ])
    ->set('data.unit_kerja_id', $laporan->unit_kerja_id)
    ->call('submitLaporan')
    ->assertHasNoErrors();

    // Re-fetch to check if status changed
    $laporan->refresh();
    
    expect($laporan->status)->toBe('dilaporkan');
});

it('can simulate full end-to-end workflow from draft to selesai', function () {
    // 1. Pelapor membuat laporan baru (Draft)
    $pelapor = User::factory()->create();
    $pelapor->assignRole('user');
    
    $kepalaUnit = User::factory()->create();
    $kepalaUnit->assignRole('kepala_unit');
    
    $timMutu = User::factory()->create();
    $timMutu->assignRole('tim_mutu');

    $laporan = LaporanInsiden::factory()->create([
        'user_id' => $pelapor->id,
        'status' => 'draft',
        'nama_pelapor' => 'Test E2E',
        'unit_kerja_id' => \App\Models\UnitKerja::factory()->create()->id,
        'tanggal_lapor' => now()->toDateString(),
        'jenis_insiden' => 'KNC (Kejadian Nyaris Cedera)',
        'tanggal_insiden' => now()->toDateString(),
        'waktu_insiden' => '10:00:00',
        'lokasi_insiden' => 'Ruang IGD',
        'insiden_menyangkut_pasien' => 'pasien_rawat_inap',
        'pelapor_insiden_pasien' => 'dokter',
        'spesialisasi_pasien' => 'penyakit_dalam',
        'insiden_terjadi_pada' => 'Pasien',
        'kategori_insiden' => 'Medication / Cairan IV',
        'deskripsi_kategori_insiden' => 'Testing desc',
        'dampak_insiden' => 'Tidak ada cedera',
        'tindakan_dilakukan' => 'P3K',
        'tindakan_dilakukan_oleh' => 'Dokter',
        'kejadian_pernah_terjadi_sebelumnya' => 'Ya',
        'kejadian_pernah_terjadi_sebelumnya_deskripsi' => 'test',
    ]);
    $laporan->timelineEvents()->create([
        'title' => 'Kejadian',
        'event_datetime' => now(),
        'description' => 'Test',
    ]);

    // 2. Pelapor Submit
    $this->actingAs($pelapor);
    $livewire = Livewire::test(EditLaporanInsiden::class, ['record' => $laporan->getRouteKey()])
        ->set('data.unit_kerja_id', $laporan->unit_kerja_id)
        ->call('submitLaporan');
    
    expect($laporan->fresh()->status)->toBe('dilaporkan');

    // 3. Kepala Unit Memverifikasi
    $this->actingAs($kepalaUnit);
    $livewire = Livewire::test(EditLaporanInsiden::class, ['record' => $laporan->getRouteKey()])
        ->set('data.unit_kerja_id', $laporan->unit_kerja_id)
        ->set('data.grading_risiko', 'Merah')
        ->call('verifikasiLaporan');

    expect($laporan->fresh()->status)->toBe('diverifikasi');
    expect($laporan->fresh()->grading_risiko)->toBe('Merah');

    // 4. Tim Mutu Memulai Investigasi
    $this->actingAs($timMutu);
    $livewire = Livewire::test(EditLaporanInsiden::class, ['record' => $laporan->getRouteKey()])
        ->set('data.unit_kerja_id', $laporan->unit_kerja_id)
        ->call('mulaiInvestigasi');

    expect($laporan->fresh()->status)->toBe('investigasi');
    expect($laporan->fresh()->investigation->investigation_started_by)->toBe($timMutu->id);

    // 5. Tim Mutu Menyelesaikan Investigasi
    $livewire = Livewire::test(EditLaporanInsiden::class, ['record' => $laporan->getRouteKey()])
        ->set('data.unit_kerja_id', $laporan->unit_kerja_id)
        ->call('selesaikanInvestigasi');

    expect($laporan->fresh()->status)->toBe('selesai');
    expect($laporan->fresh()->investigation->investigation_completed_by)->toBe($timMutu->id);
});
