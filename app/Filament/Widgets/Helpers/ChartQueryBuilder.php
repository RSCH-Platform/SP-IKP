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

    protected ?int $yearFilter = null;

    protected string $grouping = 'none';

    protected ?int $periodFilter = null;

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

    public function withYearFilter(?int $yearFilter): self
    {
        $this->yearFilter = $yearFilter;

        return $this;
    }

    public function withPeriodFilter(?string $grouping, ?int $periodFilter): self
    {
        // Only set grouping if it's a valid period type (semester or quarter)
        // If 'none', keep it as is for full year view
        if ($grouping === 'semester') {
            $this->grouping = 'semester';
        } elseif ($grouping === 'quarter') {
            $this->grouping = 'quarter';
        } elseif ($grouping !== 'none') {
            $this->grouping = 'quarter'; // Default fallback to quarter
        }
        // else: if 'none', grouping stays as 'none' or current value

        $this->periodFilter = $periodFilter;

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

        // Apply date filters
        $this->applyDateFilters();

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

    /**
     * Apply tahun / quarter / semester filters ke query
     */
    protected function applyDateFilters(): void
    {
        $this->query->whereNotNull('tanggal_insiden');

        if ($this->yearFilter) {
            $this->query->whereYear('tanggal_insiden', $this->yearFilter);
        }

        // Only apply period filters if grouping is not 'none' and period is valid
        if ($this->grouping === 'none' || !$this->periodFilter || $this->periodFilter <= 0) {
            return;
        }

        [$startMonth, $endMonth] = $this->resolveMonthRange();

        $this->query->whereRaw('MONTH(tanggal_insiden) BETWEEN ? AND ?', [$startMonth, $endMonth]);
    }

    /**
     * Resolve month range berdasarkan grouping dan periode.
     *
     * @return array{0:int,1:int}
     */
    protected function resolveMonthRange(): array
    {
        if ($this->grouping === 'semester') {
            return $this->periodFilter === 2 ? [7, 12] : [1, 6];
        }

        return match ($this->periodFilter) {
            2 => [4, 6],
            3 => [7, 9],
            4 => [10, 12],
            default => [1, 3],
        };
    }
}
