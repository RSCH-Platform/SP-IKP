<?php

namespace Database\Seeders;

use App\Models\TimelineCategory;
use Illuminate\Database\Seeder;

class TimelineCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'kejadian', 'name' => 'Kejadian', 'sort_order' => 1],
            ['code' => 'informasi', 'name' => 'Informasi Tambahan', 'sort_order' => 2],
            ['code' => 'good_practice', 'name' => 'Good Practice', 'sort_order' => 3],
            ['code' => 'cmp', 'name' => 'Masalah CMP', 'sort_order' => 4],
            ['code' => 'sdp', 'name' => 'Masalah SDP', 'sort_order' => 5],
        ];

        foreach ($categories as $cat) {
            TimelineCategory::updateOrCreate(
                ['code' => $cat['code']],
                ['name' => $cat['name'], 'sort_order' => $cat['sort_order']]
            );
        }

        $this->command->info('Timeline categories seeded: ' . count($categories));
    }
}
