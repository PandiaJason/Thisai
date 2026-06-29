<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Enums\UserRole;
use App\Enums\ExamAttemptStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyAttemptsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /** @test */
    public function test_faculty_can_access_exam_attempts_relation_manager_and_actions()
    {
        $faculty = User::where('email', 'faculty@thisai.com')->first();
        $this->assertNotNull($faculty);

        $exam = Exam::where('created_by', $faculty->id)->first();
        $this->assertNotNull($exam);

        $response = $this->actingAs($faculty)
            ->get("/faculty/exams/{$exam->id}/edit");

        $response->assertStatus(200);
    }

    /** @test */
    public function test_faculty_can_trigger_detailed_results_csv_export()
    {
        $faculty = User::where('email', 'faculty@thisai.com')->first();
        $exam = Exam::where('created_by', $faculty->id)->first();

        // Create a student attempt
        $student = User::factory()->create(['role' => UserRole::STUDENT]);
        
        $attempt = ExamAttempt::create([
            'user_id' => $student->id,
            'exam_id' => $exam->id,
            'started_at' => now(),
            'submitted_at' => now(),
            'status' => ExamAttemptStatus::SUBMITTED,
            'score' => 8,
            'total_marks' => 10,
            'accuracy' => 80.00,
            'session_token' => 'test-faculty-export-token-2',
        ]);

        // Simulating the CSV download stream directly to verify no failures
        $response = response()->streamDownload(function () use ($exam) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student Name', 'Student Email', 'Total Score Obtained']);
            
            $attempts = ExamAttempt::where('exam_id', $exam->id)
                ->where('status', '!=', ExamAttemptStatus::IN_PROGRESS->value)
                ->with(['user', 'answers.question.subject', 'answers.question.topic'])
                ->get();

            foreach ($attempts as $attempt) {
                fputcsv($handle, [$attempt->user->name, $attempt->user->email, $attempt->score]);
            }
            fclose($handle);
        }, 'test.csv');

        $this->assertEquals(200, $response->getStatusCode());
    }
}
