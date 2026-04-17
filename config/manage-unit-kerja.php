<?php

use App\Filament\Resources\UnitKerjas\UnitKerjaResource;
use App\Models\UnitKerja;

return [
    'model' => [
        'unit_kerja' => UnitKerja::class,
        'user' => \App\Models\User::class,
    ],

    'filament' => [
        'active' => true,
        'resources' => [
            UnitKerjaResource::class,
        ],
    ],

    'app_env' => env('MANAGE_UNIT_KERJA_APP_ENV', env('APP_ENV', 'production')),

    'center_application' => env('MANAGE_UNIT_KERJA_CENTER_APPLICATION', false),

    'app_center_url' => env('IAM_HOST', 'http://192.168.1.9:8100'),               

    'sync' => [
        'active' => env('MANAGE_UNIT_KERJA_SYNC_ACTIVE', true),
    ],

    'navigation_sort' => 0,
];
k