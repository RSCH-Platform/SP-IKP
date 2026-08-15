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
        
        $year = $request->input('year');
        $month = $request->input('month');

        $query = LaporanInsiden::query()
            ->when(filled($year), fn($q) => $q->whereYear('tanggal_insiden', (int)$year))
            ->when(filled($month), fn($q) => $q->whereMonth('tanggal_insiden', (int)$month));

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

        $period = 'Semua Periode';
        if (filled($year) && filled($month)) {
            $period = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
        } elseif (filled($year)) {
            $period = 'Tahun ' . $year;
        } elseif (filled($month)) {
            $period = 'Bulan ' . Carbon::createFromDate(null, $month, 1)->translatedFormat('F');
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
