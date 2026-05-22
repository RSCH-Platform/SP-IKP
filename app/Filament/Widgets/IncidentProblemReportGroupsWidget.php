<?php

namespace App\Filament\Widgets;

use App\Models\IncidentProblem;
use App\Models\LaporanInsiden;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class IncidentProblemReportGroupsWidget extends Widget
{
    use HasWidgetShield;

    protected static ?int $sort = 11;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.incident-problem-report-groups';

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user !== null && (
            $user->can('ViewAllData:LaporanInsiden')
            || $user->can('ForceEdit:LaporanInsiden')
            || $user->can('Submit:LaporanInsiden')
        );
    }

    protected function scopedQuery(): Builder
    {
        $query = LaporanInsiden::query();
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->can('ViewAllData:LaporanInsiden')) {
            return $query;
        }

        if ($user->can('ForceEdit:LaporanInsiden')) {
            $unitIds = $user->unitKerjas()->pluck('id');

            return $query->whereIn('unit_kerja_id', $unitIds);
        }

        if ($user->can('Submit:LaporanInsiden')) {
            return $query->where('user_id', $user->getKey());
        }

        return $query->whereRaw('1 = 0');
    }

    protected function getViewData(): array
    {
        $reports = $this->scopedQuery()
            ->with([
                'problems.whys',
                'problems.recommendations',
                'problems.actions.media',
            ])
            ->orderByDesc('tanggal_lapor')
            ->orderByDesc('created_at')
            ->get();

        $items = $reports->map(function (LaporanInsiden $report): array {
            $problems = $report->problems->map(function (IncidentProblem $problem): array {
                $recommendations = $problem->recommendations->map(fn ($recommendation): array => [
                    'id' => $recommendation->id,
                    'text' => $recommendation->recommendation_text,
                    'priority' => $recommendation->priority ?: 'normal',
                ])->values();

                $actions = $problem->actions->map(function ($action): array {
                    return [
                        'id' => $action->id,
                        'text' => $action->action_text,
                        'responsible_person' => $action->responsible_person,
                        'deadline' => $action->deadline?->format('d M Y'),
                        'status' => $action->status ?: 'pending',
                        'status_label' => $this->actionStatusLabel($action->status),
                        'status_color' => $this->actionStatusColor($action->status),
                        'status_badge_classes' => $this->actionStatusBadgeClasses($action->status),
                        'media_count' => $action->getMedia('action_evidence')->count(),
                    ];
                })->values();

                $completedActions = $actions->where('status', 'completed')->count();
                $ongoingActions = $actions->where('status', 'ongoing')->count();
                $pendingActions = $actions->where('status', 'pending')->count();

                return [
                    'id' => $problem->id,
                    'problem_type' => $problem->problem_type,
                    'problem_description' => $problem->problem_description,
                    'whys_count' => $problem->whys->count(),
                    'recommendations_count' => $recommendations->count(),
                    'actions_count' => $actions->count(),
                    'completed_actions_count' => $completedActions,
                    'ongoing_actions_count' => $ongoingActions,
                    'pending_actions_count' => $pendingActions,
                    'recommendations' => $recommendations->all(),
                    'actions' => $actions->all(),
                ];
            })->values();

            $totalActions = $problems->sum('actions_count');
            $completedActions = $problems->sum('completed_actions_count');
            $ongoingActions = $problems->sum('ongoing_actions_count');
            $pendingActions = $problems->sum('pending_actions_count');
            $recommendationsCount = $problems->sum('recommendations_count');
            $completionPercent = $totalActions > 0
                ? (int) round(($completedActions / $totalActions) * 100)
                : 0;

            return [
                'id' => $report->id,
                'nomor_laporan' => $report->nomor_laporan ?? '-',
                'unit_kerja' => $report->unit_kerja ?? $report->unitKerjas?->unit_name ?? '-',
                'jenis_insiden' => $report->jenis_insiden ?? '-',
                'tanggal_lapor' => optional($report->tanggal_lapor)->format('d M Y') ?? '-',
                'status' => $report->status,
                'status_label' => $this->reportStatusLabel($report->status),
                'status_color' => $this->reportStatusColor($report->status),
                'status_badge_classes' => $this->reportStatusBadgeClasses($report->status),
                'problem_count' => $problems->count(),
                'recommendations_count' => $recommendationsCount,
                'actions_count' => $totalActions,
                'completed_actions_count' => $completedActions,
                'ongoing_actions_count' => $ongoingActions,
                'pending_actions_count' => $pendingActions,
                'completion_percent' => $completionPercent,
                'problems' => $problems->all(),
            ];
        });

        return [
            'reports' => $items->all(),
            'summary' => [
                'reports_count' => $items->count(),
                'problem_count' => $items->sum('problem_count'),
                'recommendations_count' => $items->sum('recommendations_count'),
                'completed_actions_count' => $items->sum('completed_actions_count'),
                'pending_actions_count' => $items->sum('pending_actions_count'),
            ],
        ];
    }

    protected function reportStatusLabel(?string $status): string
    {
        return match ($status) {
            LaporanInsiden::STATUS_DRAFT => 'Draft',
            LaporanInsiden::STATUS_DILAPORKAN => 'Dilaporkan',
            LaporanInsiden::STATUS_REVISI => 'Revisi',
            LaporanInsiden::STATUS_DIVERIFIKASI => 'Diverifikasi',
            LaporanInsiden::STATUS_REVISI_UNIT => 'Revisi Unit',
            LaporanInsiden::STATUS_INVESTIGASI => 'Investigasi',
            LaporanInsiden::STATUS_SELESAI => 'Selesai',
            default => ucfirst((string) $status),
        };
    }

    protected function reportStatusColor(?string $status): string
    {
        return match ($status) {
            LaporanInsiden::STATUS_SELESAI => 'emerald',
            LaporanInsiden::STATUS_INVESTIGASI => 'blue',
            LaporanInsiden::STATUS_DIVERIFIKASI => 'cyan',
            LaporanInsiden::STATUS_REVISI, LaporanInsiden::STATUS_REVISI_UNIT => 'amber',
            LaporanInsiden::STATUS_DRAFT => 'slate',
            default => 'gray',
        };
    }

    protected function reportStatusBadgeClasses(?string $status): string
    {
        return match ($status) {
            LaporanInsiden::STATUS_SELESAI => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
            LaporanInsiden::STATUS_INVESTIGASI => 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',
            LaporanInsiden::STATUS_DIVERIFIKASI => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300',
            LaporanInsiden::STATUS_REVISI, LaporanInsiden::STATUS_REVISI_UNIT => 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
            LaporanInsiden::STATUS_DRAFT => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        };
    }

    protected function actionStatusLabel(?string $status): string
    {
        return match ($status) {
            'completed' => 'Selesai',
            'ongoing' => 'Berjalan',
            'pending' => 'Belum',
            default => ucfirst((string) $status),
        };
    }

    protected function actionStatusColor(?string $status): string
    {
        return match ($status) {
            'completed' => 'emerald',
            'ongoing' => 'blue',
            'pending' => 'slate',
            default => 'gray',
        };
    }

    protected function actionStatusBadgeClasses(?string $status): string
    {
        return match ($status) {
            'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
            'ongoing' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',
            'pending' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        };
    }
}