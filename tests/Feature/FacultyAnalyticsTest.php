<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Exam;
use App\Models\Subject;
use App\Enums\UserRole;
use App\Enums\ExamType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Filament\Faculty\Pages\FacultyAnalyticsPage;
use Tests\TestCase;

class FacultyAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /** @test */
    public function test_super_admin_can_access_exam_analytics_page_and_see_all_exams()
    {
        $admin = User::where('email', 'admin@thisai.com')->first();
        $this->assertNotNull($admin);

        // Access analytics page in admin panel context
        $response = $this->actingAs($admin)->get('/admin/exam-analytics');
        $response->assertStatus(200);

        // Assert using Livewire that all exams are available to Super Admin
        Livewire::actingAs($admin)
            ->test(FacultyAnalyticsPage::class)
            ->assertSet('examOptions', function ($options) {
                // Ensure options include the seeded mock test exams
                return count($options) > 0;
            });
    }

    /** @test */
    public function test_faculty_can_access_exam_analytics_page_and_see_only_their_exams()
    {
        $faculty = User::where('email', 'faculty@thisai.com')->first();
        $this->assertNotNull($faculty);

        // Faculty loads page successfully
        $response = $this->actingAs($faculty)->get('/faculty/exam-analytics');
        $response->assertStatus(200);

        // Create an exam under another user
        $anotherFaculty = User::factory()->create(['role' => UserRole::FACULTY]);
        $subject = Subject::first();

        $anotherExam = Exam::create([
            'title' => 'Other Exam Topic',
            'slug' => 'other-exam-topic-unique',
            'created_by' => $anotherFaculty->id,
            'subject_id' => $subject->id,
            'type' => ExamType::DAILY_QUIZ,
            'difficulty' => 'easy',
            'duration_minutes' => 5,
            'total_marks' => 10,
            'negative_marking' => 0.33,
            'randomize_questions' => true,
            'randomize_options' => true,
            'is_published' => true,
        ]);

        // Assert using Livewire that faculty only see their own exams
        Livewire::actingAs($faculty)
            ->test(FacultyAnalyticsPage::class)
            ->assertSet('examOptions', function ($options) use ($anotherExam) {
                return !array_key_exists($anotherExam->id, $options);
            });
    }
}
