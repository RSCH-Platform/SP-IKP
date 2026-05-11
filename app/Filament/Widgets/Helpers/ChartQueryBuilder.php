<?php

namespace App\Filament\Widgets\Helpers;

use App\Models\LaporanInsiden;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Membangun query yang sudah ter-filter berdasarkan permission dan status
 */
class ChartQueryBuilder
{
    protected Builder $query;

    protected ?User $user = null;

    /** @var array<string> */
    protected array $statusFilter = [];

    public function __construct()
    {
        $this->query = LaporanInsiden::query();
        $this->user = Auth::user();
    }

    /**
     * Set status filter
     *
     * @param array<string> $statusFilter
     */
    public function withStatusFilter(array $statusFilter): self
    {
        $this->statusFilter = $statusFilter;

        return $this;
    }

    /**
     * Build the final query dengan semua filter
     */
    public function build(): Builder
    {
        // Apply permission-based filtering
        $this->applyPermissionFilters();

        // Apply status filters
        $this->applyStatusFilters();

        return $this->query;
    }

    /**
     * Apply permission-based filters ke query
     */
    protected function applyPermissionFilters(): void
    {
        if (! $this->user) {
            $this->query->whereRaw('1 = 0');

            return;
        }

        if ($this->user->can('ViewAllData:LaporanInsiden')) {
            // No restriction
        } elseif ($this->user->can('ForceEdit:LaporanInsiden')) {
            $unitIds = $this->user->unitKerjas()->pluck('id');
            $this->query->whereIn('unit_kerja_id', $unitIds);
        } elseif ($this->user->can('Submit:LaporanInsiden')) {
            $this->query->where('user_id', $this->user->getKey());
        } else {
            $this->query->whereRaw('1 = 0');
        }
    }

    /**
     * Apply status filters ke query
     */
    protected function applyStatusFilters(): void
    {
        if (empty($this->statusFilter)) {
            return;
        }

        $this->query->where(function (Builder $q) {
            $simpleStatuses = [];
            $investigasiRequested = false;
            $selesaiInvestigasiRequested = false;

            foreach ($this->statusFilter as $status) {
                if ($status === 'investigasi') {
                    $investigasiRequested = true;
                } elseif ($status === 'selesai_investigasi') {
                    $selesaiInvestigasiRequested = true;
                } else {
                    $simpleStatuses[] = $status;
                }
            }

            // Handle investigation statuses dengan special logic
            if ($investigasiRequested || $selesaiInvestigasiRequested) {
                if ($investigasiRequested && $selesaiInvestigasiRequested) {
                    // Both: sedang investigasi OR sudah investigasi
                    $q->where('status', LaporanInsiden::STATUS_INVESTIGASI);
                } elseif ($investigasiRequested) {
                    // Only sedang investigasi
                    $q->where('status', LaporanInsiden::STATUS_INVESTIGASI)
                        ->whereNull('investigation_completed_at');
                } elseif ($selesaiInvestigasiRequested) {
                    // Only sudah investigasi
                    $q->whereNotNull('investigation_completed_at');
                }

                // Add simple statuses dengan OR
                if (!empty($simpleStatuses)) {
                    $q->orWhereIn('status', $simpleStatuses);
                }
            } elseif (!empty($simpleStatuses)) {
                // Only simple statuses
                $q->whereIn('status', $simpleStatuses);
            }
        });
    }
}
