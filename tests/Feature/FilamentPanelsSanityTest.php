<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentPanelsSanityTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * Test that Admin panel pages load successfully when authenticated as admin.
     */
    public function test_admin_panel_pages_load_successfully(): void
    {
        $admin = User::where('email', 'admin@thisai.com')->first();
        $this->assertNotNull($admin, 'The default admin user must be seeded.');

        $pages = [
            'dashboard' => '/admin',
            'users' => '/admin/users',
            'users_create' => '/admin/users/create',
            'batches' => '/admin/batches',
            'categories' => '/admin/categories',
            'subjects' => '/admin/subjects',
            'courses' => '/admin/courses',
            'courses_create' => '/admin/courses/create',
            'exams' => '/admin/exams',
            'exams_create' => '/admin/exams/create',
            'current-affairs' => '/admin/current-affairs',
            'current-affairs_create' => '/admin/current-affairs/create',
            'live-telecasts' => '/admin/live-telecasts',
            'live-telecasts_create' => '/admin/live-telecasts/create',
            'audit-logs' => '/admin/audit-logs',
        ];

        foreach ($pages as $name => $uri) {
            $response = $this->actingAs($admin)->get($uri);
            $response->assertStatus(200, "Admin page '{$name}' at URI '{$uri}' failed to load with status 200.");
        }
    }

    /**
     * Test that Faculty panel pages load successfully when authenticated as faculty.
     */
    public function test_faculty_panel_pages_load_successfully(): void
    {
        $faculty = User::where('email', 'faculty@thisai.com')->first();
        $this->assertNotNull($faculty, 'The default faculty user must be seeded.');

        $pages = [
            'dashboard' => '/faculty',
            'courses' => '/faculty/courses',
            'courses_create' => '/faculty/courses/create',
            'videos' => '/faculty/videos',
            'videos_create' => '/faculty/videos/create',
            'exams' => '/faculty/exams',
            'exams_create' => '/faculty/exams/create',
            'questions' => '/faculty/questions',
            'questions_create' => '/faculty/questions/create',
            'current-affairs' => '/faculty/current-affairs',
            'current-affairs_create' => '/faculty/current-affairs/create',
            'live-telecasts' => '/faculty/live-telecasts',
            'live-telecasts_create' => '/faculty/live-telecasts/create',
        ];

        foreach ($pages as $name => $uri) {
            $response = $this->actingAs($faculty)->get($uri);
            $response->assertStatus(200, "Faculty page '{$name}' at URI '{$uri}' failed to load with status 200.");
        }
    }
}
