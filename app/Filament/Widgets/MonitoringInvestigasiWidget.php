<?php

namespace App\Filament\Widgets;

use App\Models\LaporanInsiden;
use App\Models\UnitKerja;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class MonitoringInvestigasiWidget extends Widget
{
    protected string $view = 'filament.widgets.monitoring-investigasi-widget';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    
    public ?string $month = null;
    public ?string $quarter = null;
    public ?string $unit_id = null;
    public ?string $compliance_status = null;
    public string $year;
    
    public string $sortColumn = 'raw_tanggal';
    public string $sortDirection = 'asc';

    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function mount()
    {
        $this->month = date('m');
        $this->year = date('Y');
        $this->quarter = null;
        $this->unit_id = null;
        $this->compliance_status = '';
    }

    public function updatedMonth()
    {
        if ($this->month) {
            $this->quarter = null;
        }
    }

    public function updatedQuarter()
    {
        if ($this->quarter) {
            $this->month = null;
        }
    }

    public function getMonthsProperty(): array
    {
        return [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
    }

    public function getQuartersProperty(): array
    {
        return [
            '1' => 'Kuartal 1 (Jan - Mar)',
            '2' => 'Kuartal 2 (Apr - Jun)',
            '3' => 'Kuartal 3 (Jul - Sep)',
            '4' => 'Kuartal 4 (Okt - Des)',
        ];
    }

    public function getUnitsProperty(): array
    {
        return UnitKerja::pluck('unit_name', 'id')->toArray();
    }

    public function getYearsProperty(): array
    {
        $currentYear = (int) date('Y');
        return [
            (string) ($currentYear - 2) => (string) ($currentYear - 2),
            (string) ($currentYear - 1) => (string) ($currentYear - 1),
            (string) $currentYear => (string) $currentYear,
        ];
    }

    public function getIncidentsDataProperty(): Collection
    {
        $query = LaporanInsiden::with(['unitKerja', 'investigation'])
            ->whereIn('status', [LaporanInsiden::STATUS_INVESTIGASI, LaporanInsiden::STATUS_SELESAI])
            ->whereYear('tanggal_insiden', $this->year)
            ->orderBy('tanggal_insiden', 'asc');

        $user = auth()->user();
        if ($user && !$user->can('ViewAllData:LaporanInsiden')) {
            if ($user->can('ForceEdit:LaporanInsiden')) {
                $unitIds = $user->unitKerjas()->pluck('id');
                $query->where(function ($q) use ($unitIds) {
                    $q->whereIn('unit_kerja_id', $unitIds)
                      ->orWhere('status', LaporanInsiden::STATUS_SELESAI);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($this->month) {
            $query->whereMonth('tanggal_insiden', $this->month);
        } elseif ($this->quarter) {
            $startMonth = ($this->quarter - 1) * 3 + 1;
            $endMonth = $startMonth + 2;
            $query->whereMonth('tanggal_insiden', '>=', $startMonth)
                  ->whereMonth('tanggal_insiden', '<=', $endMonth);
        }

        if ($this->unit_id) {
            $query->where('unit_kerja_id', $this->unit_id);
        }

        $incidents = $query->get();

        $mapped = $incidents->map(function ($incident) {
            $gradingLabel = $incident->grading_risiko ?? 'Belum ada';
            $gradingColor = 'gray';
            $targetHari = 14; // Default untuk Biru/Hijau
            
            if (stripos($gradingLabel, 'Biru') !== false) {
                $gradingColor = 'blue';
                $targetHari = 7;
            } elseif (stripos($gradingLabel, 'Hijau') !== false) {
                $gradingColor = 'green';
                $targetHari = 14;
            } elseif (stripos($gradingLabel, 'Kuning') !== false) {
                $gradingColor = 'yellow';
                $targetHari = 45;
            } elseif (stripos($gradingLabel, 'Merah') !== false || stripos($gradingLabel, 'Hitam') !== false) {
                $gradingColor = 'red';
                $targetHari = 45;
            }

            $selesai = $incident->status === LaporanInsiden::STATUS_SELESAI || $incident->isInvestigationCompleted();
            
            $startedAt = $incident->investigation?->investigation_started_at ? Carbon::parse($incident->investigation->investigation_started_at) : null;
            $completedAt = $incident->investigation?->investigation_completed_at ? Carbon::parse($incident->investigation->investigation_completed_at) : null;
            
            $lamaInvestigasi = null;
            if ($startedAt && $completedAt) {
                $lama = (int) $startedAt->startOfDay()->diffInDays($completedAt->startOfDay(), false);
                $lamaInvestigasi = max(0, $lama);
            } elseif ($startedAt) {
                $lama = (int) $startedAt->startOfDay()->diffInDays(now()->startOfDay(), false);
                $lamaInvestigasi = max(0, $lama);
            }
            
            $isSesuaiTarget = false;
            if ($lamaInvestigasi !== null) {
                $isSesuaiTarget = $lamaInvestigasi <= $targetHari;
            }

            return (object) [
                'unit' => $incident->unitKerja?->unit_name ?? $incident->unit_kerja ?? '-',
                'raw_tanggal' => $incident->tanggal_insiden->timestamp,
                'tanggal' => $incident->tanggal_insiden->format('d/m/Y'),
                'jenis_insiden' => $incident->jenis_insiden ?? '-',
                'insiden' => $incident->deskripsi_kategori_insiden ?? $incident->kategori_insiden ?? '-',
                'grading' => $gradingLabel,
                'gradingColor' => $gradingColor,
                'target_hari' => $targetHari,
                'lama_investigasi' => $lamaInvestigasi,
                'is_sesuai_target' => $isSesuaiTarget,
                'is_selesai' => $selesai,
                'has_started' => !empty($startedAt),
                'unit_id' => $incident->unit_kerja_id ?? $incident->unit_kerja,
            ];
        });

        if ($this->compliance_status === 'sesuai') {
            $mapped = $mapped->filter(fn($item) => $item->is_sesuai_target === true && $item->has_started);
        } elseif ($this->compliance_status === 'tidak_sesuai') {
            $mapped = $mapped->filter(fn($item) => $item->is_sesuai_target === false && $item->has_started);
        }

        if ($this->sortDirection === 'asc') {
            $mapped = $mapped->sortBy($this->sortColumn);
        } else {
            $mapped = $mapped->sortByDesc($this->sortColumn);
        }

        return $mapped->values();
    }

    public function getSummaryDataProperty(): Collection
    {
        $incidents = $this->incidentsData;
        
        $summary = [];
        foreach ($incidents as $inc) {
            $unitKey = $inc->unit_id;
            
            if (!isset($summary[$unitKey])) {
                $summary[$unitKey] = [
                    'unit' => $inc->unit,
                    'total' => 0,
                    'sudah' => 0,
                    'belum' => 0,
                    'sesuai' => 0,
                ];
            }
            
            $summary[$unitKey]['total']++;
            
            if ($inc->is_selesai) {
                $summary[$unitKey]['sudah']++;
            } else {
                $summary[$unitKey]['belum']++;
            }
            
            // Sesuai = Waktu investigasi <= Target Hari
            if ($inc->has_started && $inc->is_sesuai_target) {
                $summary[$unitKey]['sesuai']++;
            }
        }
        
        return collect($summary)->map(function ($data) {
            // Kita hitung persen dari yang sudah mulai investigasi atau dari total insiden?
            // Sesuai dengan instruksi awal, persentase dari total insiden.
            $percentage = $data['total'] > 0 ? round(($data['sesuai'] / $data['total']) * 100) : 0;
            return (object) [
                'unit' => $data['unit'],
                'total' => $data['total'],
                'sudah' => $data['sudah'],
                'belum' => $data['belum'],
                'sesuai' => $data['sesuai'],
                'persentase' => $percentage,
            ];
        })->sortByDesc('total')->values();
    }
}
