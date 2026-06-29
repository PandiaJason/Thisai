<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = Subject::pluck('id', 'name')->toArray();

        $polityId = $subjects['Polity'] ?? null;
        $historyId = $subjects['History'] ?? null;
        $geographyId = $subjects['Geography'] ?? null;
        $economicsId = $subjects['Economics'] ?? null;
        $environmentId = $subjects['Environment'] ?? null;
        $scienceId = $subjects['Science and Technology'] ?? null;

        $badges = [
            [
                'name' => 'Polity Pundit',
                'slug' => 'polity-pundit',
                'description' => 'Score 90% or above on 3 Polity tests.',
                'points' => 300,
                'criteria_type' => 'subject_mastery',
                'criteria_value' => ['subject_id' => $polityId, 'accuracy' => 90, 'count' => 3],
                'icon_svg' => '<svg class="w-12 h-12 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16"/><rect width="16" height="14" x="4" y="4" rx="2"/><path d="M12 4v14"/><path d="M8 8h8"/><path d="M8 12h8"/></svg>',
            ],
            [
                'name' => 'History Scholar',
                'slug' => 'history-scholar',
                'description' => 'Score 90% or above on 3 History tests.',
                'points' => 300,
                'criteria_type' => 'subject_mastery',
                'criteria_value' => ['subject_id' => $historyId, 'accuracy' => 90, 'count' => 3],
                'icon_svg' => '<svg class="w-12 h-12 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 17.5 3 6V3h3l11.5 11.5"/><path d="M13 19a2 2 0 0 0 4 0v-5a2 2 0 0 0-4 0Z"/><path d="M19 9a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2Z"/><path d="M16.5 17.5 19 20h3"/></svg>',
            ],
            [
                'name' => 'Geography Explorer',
                'slug' => 'geography-explorer',
                'description' => 'Score 90% or above on 3 Geography tests.',
                'points' => 300,
                'criteria_type' => 'subject_mastery',
                'criteria_value' => ['subject_id' => $geographyId, 'accuracy' => 90, 'count' => 3],
                'icon_svg' => '<svg class="w-12 h-12 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>',
            ],
            [
                'name' => 'Economics Analyst',
                'slug' => 'economics-analyst',
                'description' => 'Score 90% or above on 3 Economics tests.',
                'points' => 300,
                'criteria_type' => 'subject_mastery',
                'criteria_value' => ['subject_id' => $economicsId, 'accuracy' => 90, 'count' => 3],
                'icon_svg' => '<svg class="w-12 h-12 text-purple-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>',
            ],
            [
                'name' => 'Environment Guardian',
                'slug' => 'environment-guardian',
                'description' => 'Score 90% or above on 3 Environment tests.',
                'points' => 300,
                'criteria_type' => 'subject_mastery',
                'criteria_value' => ['subject_id' => $environmentId, 'accuracy' => 90, 'count' => 3],
                'icon_svg' => '<svg class="w-12 h-12 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 3.5 2 5.5a7 7 0 0 1-10 12.5z"/><path d="M9 20H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/></svg>',
            ],
            [
                'name' => 'Science Innovator',
                'slug' => 'science-innovator',
                'description' => 'Score 90% or above on 3 Science & Tech tests.',
                'points' => 300,
                'criteria_type' => 'subject_mastery',
                'criteria_value' => ['subject_id' => $scienceId, 'accuracy' => 90, 'count' => 3],
                'icon_svg' => '<svg class="w-12 h-12 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18 10 4h4l4 14H6Z"/><path d="M3 22h18"/><path d="M9 12h6"/></svg>',
            ],
            [
                'name' => 'Accuracy Ace',
                'slug' => 'accuracy-ace',
                'description' => 'Submit any mock exam with 95% accuracy or higher.',
                'points' => 500,
                'criteria_type' => 'accuracy_ace',
                'criteria_value' => ['accuracy' => 95],
                'icon_svg' => '<svg class="w-12 h-12 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>',
            ],
            [
                'name' => 'Streak Legend',
                'slug' => 'streak-legend',
                'description' => 'Maintain a consecutive study streak of 7 days.',
                'points' => 400,
                'criteria_type' => 'streak_king',
                'criteria_value' => ['streak' => 7],
                'icon_svg' => '<svg class="w-12 h-12 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>',
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['slug' => $badge['slug']], $badge);
        }
    }
}
