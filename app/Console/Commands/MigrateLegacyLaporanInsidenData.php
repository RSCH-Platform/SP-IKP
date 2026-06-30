<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LaporanInsiden;
use App\Models\Investigation;
use App\Models\LaporanInsidenTransition;

class MigrateLegacyLaporanInsidenData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-legacy-laporan-insiden-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates legacy LaporanInsiden data to investigations and laporan_insiden_transitions tables.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting legacy data migration...');

        $laporans = LaporanInsiden::all();
        $countTransitions = 0;
        $countInvestigations = 0;

        foreach ($laporans as $laporan) {
            // 1. Migrate Investigation Data
            if ($laporan->grading_risiko || $laporan->investigation_started_at || $laporan->investigation_completed_at) {
                $status = 'pending';
                if ($laporan->investigation_completed_at) {
                    $status = 'completed';
                } elseif ($laporan->investigation_started_at) {
                    $status = 'in_progress';
                }

                Investigation::updateOrCreate(
                    ['laporan_insiden_id' => $laporan->id],
                    [
                        'grading_risiko' => $laporan->grading_risiko,
                        'status' => $status,
                        'investigation_started_by' => $laporan->investigation_started_by,
                        'investigation_started_at' => $laporan->investigation_started_at,
                        'investigation_completed_by' => $laporan->investigation_completed_by,
                        'investigation_completed_at' => $laporan->investigation_completed_at,
                    ]
                );
                $countInvestigations++;
            }

            // 2. Migrate Transitions Data
            // We want to avoid duplicates if this command is run multiple times
            // So we first clear existing transitions for this report if we are re-migrating
            LaporanInsidenTransition::where('laporan_insiden_id', $laporan->id)->delete();

            $transitions = [];
            $now = now();

            // Transition: Created
            if ($laporan->created_at) {
                $transitions[] = [
                    'laporan_insiden_id' => $laporan->id,
                    'actor_id' => $laporan->user_id, // Creator
                    'from_status' => null,
                    'to_status' => LaporanInsiden::STATUS_DRAFT,
                    'action_type' => 'created',
                    'reason' => null,
                    'created_at' => $laporan->created_at,
                    'updated_at' => $laporan->created_at,
                ];
            }

            // Transition: Reported
            if ($laporan->reported_at) {
                $transitions[] = [
                    'laporan_insiden_id' => $laporan->id,
                    'actor_id' => $laporan->reported_by ?? $laporan->user_id,
                    'from_status' => LaporanInsiden::STATUS_DRAFT,
                    'to_status' => LaporanInsiden::STATUS_DILAPORKAN,
                    'action_type' => 'submitted',
                    'reason' => null,
                    'created_at' => $laporan->reported_at,
                    'updated_at' => $laporan->reported_at,
                ];
            }

            // Transition: Verified or Rejected
            if ($laporan->verified_at) {
                $transitions[] = [
                    'laporan_insiden_id' => $laporan->id,
                    'actor_id' => $laporan->verified_by,
                    'from_status' => LaporanInsiden::STATUS_DILAPORKAN,
                    'to_status' => LaporanInsiden::STATUS_DIVERIFIKASI,
                    'action_type' => 'verified',
                    'reason' => null,
                    'created_at' => $laporan->verified_at,
                    'updated_at' => $laporan->verified_at,
                ];
            } elseif ($laporan->rejected_at) {
                $transitions[] = [
                    'laporan_insiden_id' => $laporan->id,
                    'actor_id' => $laporan->rejected_by,
                    'from_status' => LaporanInsiden::STATUS_DILAPORKAN,
                    'to_status' => LaporanInsiden::STATUS_DITOLAK,
                    'action_type' => 'rejected',
                    'reason' => $laporan->rejection_reason,
                    'created_at' => $laporan->rejected_at,
                    'updated_at' => $laporan->rejected_at,
                ];
            }

            // Transition: Investigation Started
            if ($laporan->investigation_started_at) {
                $transitions[] = [
                    'laporan_insiden_id' => $laporan->id,
                    'actor_id' => $laporan->investigation_started_by,
                    'from_status' => LaporanInsiden::STATUS_DIVERIFIKASI, // Assumed preceding status
                    'to_status' => LaporanInsiden::STATUS_INVESTIGASI,
                    'action_type' => 'investigation_started',
                    'reason' => null,
                    'created_at' => $laporan->investigation_started_at,
                    'updated_at' => $laporan->investigation_started_at,
                ];
            }

            // Transition: Investigation Completed
            if ($laporan->investigation_completed_at) {
                $transitions[] = [
                    'laporan_insiden_id' => $laporan->id,
                    'actor_id' => $laporan->investigation_completed_by,
                    'from_status' => LaporanInsiden::STATUS_INVESTIGASI,
                    'to_status' => LaporanInsiden::STATUS_SELESAI,
                    'action_type' => 'investigation_completed',
                    'reason' => null,
                    'created_at' => $laporan->investigation_completed_at,
                    'updated_at' => $laporan->investigation_completed_at,
                ];
            }

            if (count($transitions) > 0) {
                LaporanInsidenTransition::insert($transitions);
                $countTransitions += count($transitions);
            }
        }

        $this->info("Migration completed successfully!");
        $this->info("Total Investigations created/updated: {$countInvestigations}");
        $this->info("Total Transitions recorded: {$countTransitions}");
    }
}
