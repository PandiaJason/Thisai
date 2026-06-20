<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $gsCategory = Category::where('name', 'General Studies')->first();
        if (!$gsCategory) {
            return;
        }

        $subjects = [
            [
                'name' => 'Polity',
                'description' => 'Indian Constitution, Governance, Public Policy and Rights issues.',
                'color' => '#1E3A8A', // Deep Blue
                'category_id' => $gsCategory->id,
                'sort_order' => 1,
            ],
            [
                'name' => 'History',
                'description' => 'Ancient, Medieval, Modern Indian History and World History.',
                'color' => '#B45309', // Amber / Brown
                'category_id' => $gsCategory->id,
                'sort_order' => 2,
            ],
            [
                'name' => 'Geography',
                'description' => 'Physical, Social, Economic Geography of India and the World.',
                'color' => '#047857', // Forest Green
                'category_id' => $gsCategory->id,
                'sort_order' => 3,
            ],
            [
                'name' => 'Economics',
                'description' => 'Indian Economy, Economic Development, Banking, Budget and planning.',
                'color' => '#7C3AED', // Royal Purple
                'category_id' => $gsCategory->id,
                'sort_order' => 4,
            ],
            [
                'name' => 'Environment',
                'description' => 'Ecology, Biodiversity, Climate Change and Environment conservation.',
                'color' => '#15803D', // Emerald Green
                'category_id' => $gsCategory->id,
                'sort_order' => 5,
            ],
            [
                'name' => 'Science and Technology',
                'description' => 'Recent developments in IT, Space, Biotech, Nanotech and IP issues.',
                'color' => '#0369A1', // Sky Blue
                'category_id' => $gsCategory->id,
                'sort_order' => 6,
            ],
            [
                'name' => 'Current Affairs',
                'description' => 'National and international news of developmental and social importance.',
                'color' => '#BE123C', // Crimson Red
                'category_id' => $gsCategory->id,
                'sort_order' => 7,
            ]
        ];

        foreach ($subjects as $sub) {
            Subject::updateOrCreate(['name' => $sub['name']], $sub);
        }
    }
}
