<?php

namespace App\Filament\Widgets;

use App\Models\LaporanInsiden;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class InvestigatedReportsTableWidget extends Widget
{
    use HasWidgetShield;

    protected static ?int $sort = 2;

    protected static ?string $heading = 'Daftar Investigasi Selesai';

    protected static ?string $description = 'Tabel laporan yang sudah selesai diinvestigasi, ditampilkan tanpa aksi perubahan data.';

    protected string $view = 'filament.widgets.investigated-reports-table';

    protected int|string|array $columnSpan = 'full';

    public ?string $selectedYear = null;

    public ?string $selectedMonth = null;

    public string $selectedJenisInsiden = '';

    public string $selectedStatus = LaporanInsiden::STATUS_INVESTIGASI;

    public function mount(): void
    {
        $availableYears = $this->getAvailableYears();

        $this->selectedYear = (string) ($availableYears[0] ?? now()->year);
        $this->selectedMonth = null;
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user !== null && (
            $user->can('ViewAllData:LaporanInsiden')
            || $user->can('ForceEdit:LaporanInsiden')
            || $user->can('Investigasi:LaporanInsiden')
        );
    }

    protected function getViewData(): array
    {
        $reports = $this->scopedQuery()
            ->when(
                filled($this->selectedYear),
                fn (Builder $query): Builder => $query->whereYear('tanggal_insiden', (int) $this->selectedYear),
            )
            ->when(
                filled($this->selectedMonth),
                fn (Builder $query): Builder => $query->whereMonth('tanggal_insiden', (int) $this->selectedMonth),
            )
            ->when(
                filled($this->selectedJenisInsiden),
                fn (Builder $query): Builder => $query->where('jenis_insiden', $this->selectedJenisInsiden),
            )
            ->when(
                filled($this->selectedStatus),
                fn (Builder $query): Builder => $query->where('status', $this->selectedStatus),
            )
            ->with([
                'unitKerjas',
                'problems.whys',
                'problems.recommendations',
            ])
            ->latest('tanggal_insiden')
            ->get();

        $groups = $this->buildRows($reports);

        $totalRows = array_sum(array_map(fn ($g) => count($g['problems'] ?? []), $groups));

        return [
            'rows' => $groups,
            'totalReports' => $reports->count(),
            'totalRows' => $totalRows,
        ];
    }

    protected function scopedQuery(): Builder
    {
        $query = LaporanInsiden::query();
        $user = Auth::user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->can('ViewAllData:LaporanInsiden')) {
            return $query;
        }

        if ($user->can('ForceEdit:LaporanInsiden')) {
            $unitIds = $user->unitKerjas()->pluck('id');

            return $query->whereIn('unit_kerja_id', $unitIds);
        }

        if ($user->can('Investigasi:LaporanInsiden')) {
            return $query;
        }

        if ($user->can('Submit:LaporanInsiden')) {
            return $query->where('user_id', $user->getKey());
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * @param Collection<int, LaporanInsiden> $reports
     * @return array<int, array<string, string>>
     */
    protected function buildRows(Collection $reports): array
    {
        $groups = [];

        foreach ($reports as $record) {
            $baseRow = [
                'tanggal_insiden' => $this->formatTanggalInsiden($record->tanggal_insiden),
                'deskripsi_kategori_insiden' => filled($record->deskripsi_kategori_insiden)
                    ? (string) $record->deskripsi_kategori_insiden
                    : '-',
                'jenis_insiden' => filled($record->jenis_insiden)
                    ? ($this->getIncidentTypeOptions()[(string) $record->jenis_insiden] ?? (string) $record->jenis_insiden)
                    : '-',
                'unit_kerja' => $record->unit_kerja
                    ?? $record->unitKerjas?->unit_name
                    ?? '-',
            ];

            $problemRows = $this->buildProblemRows($record);

            if ($problemRows === []) {
                $problemRows = [
                    [
                        'akar_masalah' => '',
                        'rekomendasi' => '',
                    ],
                ];
            }

            $groups[] = [
                'base' => $baseRow,
                'problems' => $problemRows,
            ];
        }

        return $groups;
    }

    protected function formatTanggalInsiden(mixed $tanggalInsiden): string
    {
        if ($tanggalInsiden instanceof \DateTimeInterface) {
            return $tanggalInsiden->format('d M Y');
        }

        if (filled($tanggalInsiden)) {
            return (string) $tanggalInsiden;
        }

        return '-';
    }

    /**
     * @return array<int, array{akar_masalah: string, rekomendasi: string}>
     */
    protected function buildProblemRows(LaporanInsiden $record): array
    {
        $rows = [];

        foreach ($record->problems as $problem) {
            $latestWhyLevel = $problem->whys->max('why_level');

            $akarMasalahItems = $problem->whys
                ->when(
                    filled($latestWhyLevel),
                    fn (Collection $whys): Collection => $whys->where('why_level', $latestWhyLevel),
                )
                ->pluck('problem_statement')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $recommendationItems = $problem->recommendations
                ->pluck('recommendation_text')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $akarMasalahItems = $akarMasalahItems !== [] ? $akarMasalahItems : ['-'];
            $recommendationItems = $recommendationItems !== [] ? $recommendationItems : ['-'];

            $maxRows = max(count($akarMasalahItems), count($recommendationItems));

            for ($index = 0; $index < $maxRows; $index++) {
                $rows[] = [
                    'akar_masalah' => $akarMasalahItems[$index] ?? '-',
                    'rekomendasi' => $recommendationItems[$index] ?? '-',
                ];
            }
        }

        return $rows;
    }

    /**
     * Aggregate problem statements and recommendations into single strings.
     *
     * @return array{akar_masalah: string, rekomendasi: string}
     */
    

    public function getAvailableYears(): array
    {
        $years = LaporanInsiden::query()
            ->whereNotNull('tanggal_insiden')
            ->selectRaw('YEAR(tanggal_insiden) as year')
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->all();

        return $years !== [] ? $years : [(int) now()->year];
    }

    public function getMonthOptions(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    public function getStatusOptions(): array
    {
        return [
            '' => 'Semua status',
            LaporanInsiden::STATUS_DRAFT => 'Draft',
            LaporanInsiden::STATUS_DILAPORKAN => 'Dilaporkan',
            LaporanInsiden::STATUS_REVISI => 'Revisi',
            LaporanInsiden::STATUS_REVISI_UNIT => 'Revisi Unit',
            LaporanInsiden::STATUS_DIVERIFIKASI => 'Diverifikasi',
            LaporanInsiden::STATUS_INVESTIGASI => 'Investigasi',
            LaporanInsiden::STATUS_SELESAI => 'Selesai',
        ];
    }

    public function getIncidentTypeOptions(): array
    {
        return [
            '' => 'Semua jenis insiden',
            'KPC (Kondisi Potensial Cedera)' => 'KPC',
            'KNC (Kejadian Nyaris Cedera)' => 'KNC',
            'KTC (Kejadian Tidak Cedera)' => 'KTC',
            'KTD (Kejadian Tidak Diharapkan)' => 'KTD',
            'Sentinel' => 'Sentinel',
        ];
    }
}