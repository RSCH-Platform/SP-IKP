<?php

namespace App\Filament\Widgets;

use App\Models\IncidentProblem;
use App\Models\LaporanInsiden;
use App\Models\ProblemAction;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class IncidentProblemReportGroupsWidget extends Widget
{
    use HasWidgetShield;

    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.incident-problem-report-groups';

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user !== null && (
            $user->can('Verifikasi:LaporanInsiden')
            && !$user->hasRole('tim_mutu')
            && $user->unitKerjas()->exists()
        );
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

        $unitIds = $user->unitKerjas()->pluck('unit_kerja.id')->filter()->values();

        if ($unitIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('unit_kerja_id', $unitIds);

    }

    protected function getViewData(): array
    {
        $reports = $this->scopedQuery()
            ->with([
                'problems.whys',
                'problems.recommendations',
                'problems.actions.media',
                'unitKerjas',
            ])
            ->orderByDesc('tanggal_lapor')
            ->orderByDesc('created_at')
            ->get();

        $items = $reports->map(function (LaporanInsiden $report) {
            $problems = $report->problems
                ->filter(
                    fn(IncidentProblem $problem): bool =>
                    $problem->recommendations->isNotEmpty() && $problem->actions->isNotEmpty()
                )
                ->map(function (IncidentProblem $problem): array {
                    $recommendations = $problem->recommendations->map(fn($recommendation): array => [
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
                            'status_dot_classes' => $this->actionStatusDotClasses($action->status),
                            'status_panel_classes' => $this->actionStatusPanelClasses($action->status),
                            'media_count' => $action->getMedia('action_evidence')->count(),
                        ];
                    })->values();

                    $completedActions = $actions->where('status', 'completed')->count();
                    $ongoingActions = $actions->where('status', 'ongoing')->count();
                    $pendingActions = $actions->where('status', 'pending')->count();

                    return [
                        'id' => $problem->id,
                        'problem_type' => $problem->problem_type,
                        'problem_type_label' => $this->problemTypeLabel($problem->problem_type),
                        'problem_type_caption' => $this->problemTypeCaption($problem->problem_type),
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

            if ($problems->isEmpty()) {
                return null;
            }

            $totalActions = $problems->sum('actions_count');
            $completedActions = $problems->sum('completed_actions_count');
            $ongoingActions = $problems->sum('ongoing_actions_count');
            $pendingActions = $problems->sum('pending_actions_count');
            $recommendationsCount = $problems->sum('recommendations_count');
            $completionPercent = $totalActions > 0
                ? (int) round(($completedActions / $totalActions) * 100)
                : 0;

            if ($completionPercent === 100) {
                return null;
            }

            return [
                'id' => $report->id,
                'nomor_laporan' => $report->nomor_laporan ?? '-',
                'deskripsi_kategori_insiden' => $report->deskripsi_kategori_insiden ?? '-',
                'unit_kerja_name' => $report->unit_kerja ?? $report->unitKerjas?->unit_name ?? '-',
                'unit_kerja_id' => $report->unit_kerja_id ?? $report->unitKerjas?->id,
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
        })->filter()->values();

        // Group by unit kerja
        $units = $items->groupBy('unit_kerja_id')->map(function ($reports) {
            $unitName = $reports->first()['unit_kerja_name'] ?? '-';
            $unitId = $reports->first()['unit_kerja_id'] ?? null;
            $totalReports = $reports->count();
            $totalProblems = $reports->sum('problem_count');
            $totalActions = $reports->sum('actions_count');
            $completedActions = $reports->sum('completed_actions_count');
            $ongoingActions = $reports->sum('ongoing_actions_count');
            $pendingActions = $reports->sum('pending_actions_count');
            $totalRecommendations = $reports->sum('recommendations_count');
            $completionPercent = $totalActions > 0
                ? (int) round(($completedActions / $totalActions) * 100)
                : 0;

            return [
                'id' => $unitId,
                'unit_name' => $unitName,
                'reports_count' => $totalReports,
                'problem_count' => $totalProblems,
                'recommendations_count' => $totalRecommendations,
                'actions_count' => $totalActions,
                'completed_actions_count' => $completedActions,
                'ongoing_actions_count' => $ongoingActions,
                'pending_actions_count' => $pendingActions,
                'completion_percent' => $completionPercent,
                'reports' => $reports->values()->all(),
            ];
        })->values();

        $summary = [
            'units_count' => $units->count(),
            'reports_count' => $items->count(),
            'problem_count' => $items->sum('problem_count'),
            'recommendations_count' => $items->sum('recommendations_count'),
            'completed_actions_count' => $items->sum('completed_actions_count'),
            'pending_actions_count' => $items->sum('pending_actions_count'),
        ];

        return [
            'units' => $units->all(),
            'summary' => $summary,
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
            LaporanInsiden::STATUS_SELESAI => 'green',
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
            LaporanInsiden::STATUS_SELESAI => 'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-300',
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
            'completed' => 'Completed',
            'ongoing' => 'Ongoing',
            'pending' => 'Pending',
            default => ucfirst((string) $status),
        };
    }

    protected function actionStatusColor(?string $status): string
    {
        return match ($status) {
            'completed' => 'green',
            'ongoing' => 'blue',
            'pending' => 'warning',
            default => 'gray',
        };
    }

    protected function actionStatusBadgeClasses(?string $status): string
    {
        return match ($status) {
            'completed' => 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-950/30 dark:text-green-300 dark:ring-green-900/50',
            'ongoing' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200 dark:bg-blue-950/30 dark:text-blue-300 dark:ring-blue-900/50',
            'pending' => 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-200 dark:bg-yellow-950/30 dark:text-yellow-300 dark:ring-yellow-900/50',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:te xt-gray-300',
        };
    }

    protected function actionStatusDotClasses(?string $status): string
    {
        return match ($status) {
            'completed' => 'bg-green-500 dark:bg-green-400',
            'ongoing' => 'bg-blue-500 dark:bg-blue-400',
            'pending' => 'bg-yellow-500 dark:bg-yellow-400',
            default => 'bg-gray-500 dark:bg-gray-400',
        };
    }

    protected function actionStatusPanelClasses(?string $status): string
    {
        return match ($status) {
            'completed' => 'border-green-200 bg-green-50 text-green-700 dark:border-green-900/40 dark:bg-green-950/30 dark:text-green-300',
            'ongoing' => 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-300',
            'pending' => 'border-yellow-200 bg-yellow-50 text-yellow-700 dark:border-yellow-900/40 dark:bg-yellow-950/30 dark:text-yellow-300',
            default => 'border-slate-200 bg-slate-50 text-slate-700 dark:border-white/10 dark:bg-slate-950 dark:text-slate-300',
        };
    }

    protected function actionStatusDropdownItemClasses(bool $isActive): string
    {
        return $isActive
            ? 'bg-slate-50 text-slate-900 dark:bg-white/5 dark:text-white'
            : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5';
    }

    protected function problemTypeLabel(?string $problemType): string
    {
        return match ($problemType) {
            'CMP' => 'CMP',
            'SDP' => 'SDP',
            default => (string) $problemType,
        };
    }

    protected function problemTypeCaption(?string $problemType): string
    {
        return match ($problemType) {
            'CMP' => 'Clinical Management Problem',
            'SDP' => 'System Development Problem',
            default => '',
        };
    }

    public function updateActionStatus(int $actionId, string $status): void
    {
        if (!in_array($status, ['pending', 'ongoing', 'completed'], true)) {
            return;
        }

        $action = ProblemAction::query()->findOrFail($actionId);
        $action->update([
            'status' => $status,
        ]);

        Notification::make()
            ->title('Status tindakan diperbarui')
            ->success()
            ->send();

        $this->skipRender();
    }
}