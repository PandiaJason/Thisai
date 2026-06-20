<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'General Studies',
                'description' => 'Core subjects for UPSC Prelims and Mains examinations.',
                'icon' => 'academic-cap',
                'sort_order' => 1,
            ],
            [
                'name' => 'Optional Subjects',
                'description' => 'Specialized subjects chosen by candidates for Mains written exam.',
                'icon' => 'book-open',
                'sort_order' => 2,
            ],
            [
                'name' => 'Current Affairs & Editorials',
                'description' => 'Daily analysis of events, news articles, and editorials.',
                'icon' => 'globe-alt',
                'sort_order' => 3,
            ]
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
