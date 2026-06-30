<?php

namespace Tests\Unit\Actions;

use App\Actions\LaporanInsiden\SubmitLaporanAction;
use App\Actions\LaporanInsiden\VerifikasiLaporanAction;
use App\Actions\LaporanInsiden\KembalikanLaporanAction;
use App\Actions\LaporanInsiden\MulaiInvestigasiAction;
use App\Actions\LaporanInsiden\SelesaikanInvestigasiAction;
use App\Actions\LaporanInsiden\ReopenInvestigasiAction;
use App\Jobs\NotifyKepalaUnitForNewReportJob;
use App\Jobs\NotifyPelaporForRevisionJob;
use App\Jobs\NotifyTimMutuForInvestigationJob;
use App\Models\LaporanInsiden;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LaporanInsidenActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_submit_laporan_action_updates_status_and_dispatches_job(): void
    {
        $user = User::factory()->create();
        $laporan = LaporanInsiden::factory()->create(['status' => LaporanInsiden::STATUS_DRAFT]);

        $action = new SubmitLaporanAction();
        $action->execute($laporan, $user->id);

        $this->assertEquals(LaporanInsiden::STATUS_DILAPORKAN, $laporan->status);
        $this->assertEquals($user->id, $laporan->reported_by);
        $this->assertNotNull($laporan->reported_at);

        Queue::assertPushed(NotifyKepalaUnitForNewReportJob::class, function ($job) use ($laporan) {
            return $job->laporan->id === $laporan->id;
        });
    }

    public function test_verifikasi_laporan_action_updates_status_and_dispatches_job(): void
    {
        $user = User::factory()->create();
        $laporan = LaporanInsiden::factory()->create(['status' => LaporanInsiden::STATUS_DILAPORKAN]);

        $action = new VerifikasiLaporanAction();
        $action->execute($laporan, $user->id, 'Merah', 'Catatan penting');

        $this->assertEquals(LaporanInsiden::STATUS_DIVERIFIKASI, $laporan->status);
        $this->assertEquals('Merah', $laporan->grading_risiko);
        $this->assertEquals('Catatan penting', $laporan->catatan_tambahan);
        $this->assertEquals($user->id, $laporan->verified_by);
        $this->assertNotNull($laporan->verified_at);

        Queue::assertPushed(NotifyTimMutuForInvestigationJob::class, function ($job) use ($laporan) {
            return $job->laporan->id === $laporan->id;
        });
    }

    public function test_kembalikan_laporan_action_to_pelapor_dispatches_job(): void
    {
        $user = User::factory()->create();
        $laporan = LaporanInsiden::factory()->create(['status' => LaporanInsiden::STATUS_DILAPORKAN]);

        $action = new KembalikanLaporanAction();
        $action->execute($laporan, $user->id, 'Kurang lengkap', false);

        $this->assertEquals(LaporanInsiden::STATUS_REVISI, $laporan->status);
        $this->assertEquals('Kurang lengkap', $laporan->rejection_reason);
        $this->assertEquals($user->id, $laporan->rejected_by);
        
        Queue::assertPushed(NotifyPelaporForRevisionJob::class, function ($job) use ($laporan) {
            return $job->laporan->id === $laporan->id && $job->reason === 'Kurang lengkap';
        });
    }

    public function test_kembalikan_laporan_action_to_kepala_unit_does_not_dispatch_pelapor_job(): void
    {
        $user = User::factory()->create();
        $laporan = LaporanInsiden::factory()->create(['status' => LaporanInsiden::STATUS_DIVERIFIKASI]);

        $action = new KembalikanLaporanAction();
        $action->execute($laporan, $user->id, 'Revisi ke kepala unit', true);

        $this->assertEquals(LaporanInsiden::STATUS_REVISI_UNIT, $laporan->status);
        $this->assertEquals('Revisi ke kepala unit', $laporan->rejection_reason);
        $this->assertEquals($user->id, $laporan->rejected_by);

        Queue::assertNotPushed(NotifyPelaporForRevisionJob::class);
    }

    public function test_mulai_investigasi_action_updates_status(): void
    {
        $user = User::factory()->create();
        $laporan = LaporanInsiden::factory()->create(['status' => LaporanInsiden::STATUS_DIVERIFIKASI]);

        $action = new MulaiInvestigasiAction();
        $action->execute($laporan, $user->id);

        $laporan->refresh();
        $this->assertEquals(LaporanInsiden::STATUS_INVESTIGASI, $laporan->status);
        $this->assertEquals($user->id, $laporan->investigation->investigation_started_by);
        $this->assertNotNull($laporan->investigation->investigation_started_at);
    }

    public function test_selesaikan_investigasi_action_updates_status(): void
    {
        $user = User::factory()->create();
        $laporan = LaporanInsiden::factory()->create(['status' => LaporanInsiden::STATUS_INVESTIGASI]);
        // Simulate an existing investigation
        $laporan->investigation()->create([
            'investigation_started_by' => $user->id,
            'investigation_started_at' => now()->subDay(),
        ]);

        $action = new SelesaikanInvestigasiAction();
        $action->execute($laporan, $user->id);

        $laporan->refresh();
        $this->assertEquals(LaporanInsiden::STATUS_SELESAI, $laporan->status);
        $this->assertEquals($user->id, $laporan->investigation->investigation_completed_by);
        $this->assertNotNull($laporan->investigation->investigation_completed_at);
    }

    public function test_reopen_investigasi_action_updates_status(): void
    {
        $user = User::factory()->create();
        $laporan = LaporanInsiden::factory()->create([
            'status' => LaporanInsiden::STATUS_SELESAI,
        ]);
        $laporan->investigation()->create([
            'investigation_started_by' => $user->id,
            'investigation_started_at' => now()->subDay(),
            'investigation_completed_by' => $user->id,
            'investigation_completed_at' => now(),
        ]);

        $action = new ReopenInvestigasiAction();
        $action->execute($laporan, $user->id);

        $laporan->refresh();
        $this->assertEquals(LaporanInsiden::STATUS_INVESTIGASI, $laporan->status);
        $this->assertNull($laporan->investigation->investigation_completed_by);
        $this->assertNull($laporan->investigation->investigation_completed_at);
    }
}
