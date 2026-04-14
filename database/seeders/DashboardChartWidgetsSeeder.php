<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class DashboardChartWidgetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $widgetPermissions = [
            'view_incident_status_widget' => [
                'name' => 'view_incident_status_widget',
                'guard_name' => 'web',
                'description' => 'Can view Incident Status Widget',
            ],
            'view_incident_category_widget' => [
                'name' => 'view_incident_category_widget',
                'guard_name' => 'web',
                'description' => 'Can view Incident Category Widget',
            ],
            'view_risk_grading_widget' => [
                'name' => 'view_risk_grading_widget',
                'guard_name' => 'web',
                'description' => 'Can view Risk Grading Widget',
            ],
            'view_incident_trend_widget' => [
                'name' => 'view_incident_trend_widget',
                'guard_name' => 'web',
                'description' => 'Can view Incident Trend Widget',
            ],
        ];

        // Create permissions
        foreach ($widgetPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => $permission['guard_name']],
                $permission
            );
        }

        // Assign all widget permissions to 'panel_user' role by default
        $panelUserRole = Role::firstOrCreate(['name' => 'panel_user', 'guard_name' => 'web']);
        $permissionNames = array_keys($widgetPermissions);
        $panelUserRole->givePermissionTo($permissionNames);

        $this->command->info('Dashboard Chart Widget permissions created successfully!');
    }
}
