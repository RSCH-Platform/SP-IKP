<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RegisterDashboardWidgetPermissions extends Command
{
    protected $signature = 'app:register-dashboard-widget-permissions {--reset : Reset existing permissions}';

    protected $description = 'Register widget permissions for Filament Shield';

    public function handle(): int
    {
        $reset = $this->option('reset');

        if ($reset) {
            $this->info('Resetting widget permissions...');
            Permission::whereIn('name', [
                'view_incident_status_widget',
                'view_incident_category_widget',
                'view_risk_grading_widget',
                'view_incident_trend_widget',
            ])->delete();
        }

        $widgets = [
            'view_incident_status_widget' => 'Can view Incident Status Widget - Distribusi status laporan insiden',
            'view_incident_category_widget' => 'Can view Incident Category Widget - Top kategori insiden terbanyak',
            'view_risk_grading_widget' => 'Can view Risk Grading Widget - Distribusi grading risiko',
            'view_incident_trend_widget' => 'Can view Incident Trend Widget - Trend insiden 12 bulan terakhir',
        ];

        foreach ($widgets as $permissionName => $description) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['description' => $description]
            );
            $this->line("✓ Permission created: {$permissionName}");
        }

        // Assign to panel_user role
        $panelUserRole = Role::firstOrCreate(
            ['name' => 'panel_user', 'guard_name' => 'web'],
            ['description' => 'Panel User']
        );

        $permissionNames = array_keys($widgets);
        $panelUserRole->syncPermissions($permissionNames);

        $this->info("\n✓ All widget permissions assigned to 'panel_user' role");
        $this->info('✓ Widget permissions registered successfully!');

        return self::SUCCESS;
    }
}
