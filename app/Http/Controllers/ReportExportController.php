<?php

namespace App\Http\Controllers;

use App\Models\LaporanInsiden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportExportController extends Controller
{
    public function exportIncidentCategoryPdf(Request $request)
    {
        abort_unless(Auth::check(), 403);
        
        $periodType = $request->input('period_type', 'year');
        $year = $request->input('year');
        $month = $request->input('month');
        $startMonth = $request->input('start_month');
        $endMonth = $request->input('end_month');
        $quarter = $request->input('quarter');
        $semester = $request->input('semester');

        $query = LaporanInsiden::query();

        if (filled($year)) {
            $query->whereYear('tanggal_insiden', (int) $year);
        }

        match ($periodType) {
            'month' => filled($month)
                ? $query->whereMonth('tanggal_insiden', (int) $month)
                : $query,
            'month_range' => (filled($startMonth) && filled($endMonth))
                ? $query->whereMonth('tanggal_insiden', '>=', min((int)$startMonth, (int)$endMonth))
                        ->whereMonth('tanggal_insiden', '<=', max((int)$startMonth, (int)$endMonth))
                : $query,
            'quarter' => match ((int) $quarter) {
                1 => $query->whereMonth('tanggal_insiden', '>=', 1)->whereMonth('tanggal_insiden', '<=', 3),
                2 => $query->whereMonth('tanggal_insiden', '>=', 4)->whereMonth('tanggal_insiden', '<=', 6),
                3 => $query->whereMonth('tanggal_insiden', '>=', 7)->whereMonth('tanggal_insiden', '<=', 9),
                4 => $query->whereMonth('tanggal_insiden', '>=', 10)->whereMonth('tanggal_insiden', '<=', 12),
                default => $query,
            },
            'semester' => match ((int) $semester) {
                1 => $query->whereMonth('tanggal_insiden', '>=', 1)->whereMonth('tanggal_insiden', '<=', 6),
                2 => $query->whereMonth('tanggal_insiden', '>=', 7)->whereMonth('tanggal_insiden', '<=', 12),
                default => $query,
            },
            default => $query,
        };

        // Apply same permissions as the widget
        $user = Auth::user();
        if (!$user->can('ViewAllData:LaporanInsiden')) {
            if ($user->can('ForceEdit:LaporanInsiden')) {
                $query->whereIn('unit_kerja_id', $user->unitKerjas()->pluck('id'));
            } elseif ($user->can('Investigasi:LaporanInsiden')) {
                // can see all
            } elseif ($user->can('Submit:LaporanInsiden')) {
                $query->where('user_id', $user->getKey());
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $data = $query->whereNotNull('kategori_insiden')
            ->selectRaw('kategori_insiden, COUNT(*) as total')
            ->groupBy('kategori_insiden')
            ->orderByDesc('total')
            ->get();
            
        $totalAll = $data->sum('total');
        
        $rows = [];
        $labels = [];
        $series = [];
        foreach ($data as $row) {
            $label = $row->kategori_insiden ?: 'Lainnya';
            $rows[] = [
                'kategori' => $label,
                'total' => $row->total,
                'persentase' => $totalAll > 0 ? round(($row->total / $totalAll) * 100, 1) : 0,
            ];
            $labels[] = $label;
            $series[] = $row->total;
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $period = 'Semua Periode';
        $yearStr = filled($year) ? ' ' . $year : '';

        if ($periodType === 'month' && filled($month)) {
            $period = ($monthNames[(int) $month] ?? 'Bulan ' . $month) . $yearStr;
        } elseif ($periodType === 'month_range' && filled($startMonth) && filled($endMonth)) {
            $s = min((int) $startMonth, (int) $endMonth);
            $e = max((int) $startMonth, (int) $endMonth);
            $period = ($monthNames[$s] ?? $s) . ' - ' . ($monthNames[$e] ?? $e) . $yearStr;
        } elseif ($periodType === 'quarter' && filled($quarter)) {
            $qLabels = [
                1 => 'Kuartal 1 (Jan - Mar)',
                2 => 'Kuartal 2 (Apr - Jun)',
                3 => 'Kuartal 3 (Jul - Sep)',
                4 => 'Kuartal 4 (Okt - Des)',
            ];
            $period = ($qLabels[(int) $quarter] ?? 'Kuartal ' . $quarter) . $yearStr;
        } elseif ($periodType === 'semester' && filled($semester)) {
            $sLabels = [
                1 => 'Semester 1 (Jan - Jun)',
                2 => 'Semester 2 (Jul - Des)',
            ];
            $period = ($sLabels[(int) $semester] ?? 'Semester ' . $semester) . $yearStr;
        } elseif (filled($year)) {
            $period = 'Tahun ' . $year;
        }

        return view('reports.incident-category-print', [
            'rows' => $rows,
            'labels' => $labels,
            'series' => $series,
            'totalAll' => $totalAll,
            'period' => $period,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
            'user' => $user->name,
        ]);
    }

    public function printMonitoringInvestigasi(Request $request)
    {
        abort_unless(Auth::check(), 403);
        
        $widget = new \App\Filament\Widgets\MonitoringInvestigasiWidget();
        $widget->year = $request->input('year', date('Y'));
        $widget->month = $request->input('month');
        $widget->quarter = $request->input('quarter');
        $widget->unit_id = $request->input('unit_id');
        $widget->compliance_status = $request->input('compliance_status');
        $widget->case_status = $request->input('case_status');
        $widget->sortColumn = $request->input('sortColumn', 'raw_tanggal');
        $widget->sortDirection = $request->input('sortDirection', 'asc');

        // Verify permissions
        $user = Auth::user();
        if (!$user->can('ViewAllData:LaporanInsiden') && !$user->can('Investigasi:LaporanInsiden')) {
            abort(403);
        }

        $incidentsData = $widget->getIncidentsDataProperty();
        $summaryData = $widget->getSummaryDataProperty();

        // Calculate period label
        $periodLabel = 'Tahun ' . $widget->year;
        if ($widget->month) {
            $periodLabel = 'Bulan ' . Carbon::createFromDate(null, $widget->month, 1)->translatedFormat('F') . ' ' . $widget->year;
        } elseif ($widget->quarter) {
            $quarters = $widget->getQuartersProperty();
            $periodLabel = ($quarters[$widget->quarter] ?? 'Kuartal ' . $widget->quarter) . ' Tahun ' . $widget->year;
        }

        return view('reports.monitoring-investigasi-print', [
            'incidents' => $incidentsData,
            'summary' => $summaryData,
            'periodLabel' => $periodLabel,
            'unitLabel' => $widget->unit_id ? ($widget->getUnitsProperty()[$widget->unit_id] ?? 'Semua Unit') : 'Semua Unit',
            'statusLabel' => $widget->compliance_status === 'sesuai' ? 'Sesuai Target' : ($widget->compliance_status === 'tidak_sesuai' ? 'Tidak Sesuai Target' : 'Semua Status Waktu'),
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
            'user' => $user->name,
        ]);
    }
}
