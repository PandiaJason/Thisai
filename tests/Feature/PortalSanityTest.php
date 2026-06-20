<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalSanityTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;
    /**
     * Test that all main student portal routes load successfully when authenticated.
     */
    public function test_student_portal_pages_load_successfully(): void
    {
        // 1. Fetch the seeded candidate student
        $student = User::where('email', 'student@thisai.com')->first();
        
        $this->assertNotNull($student, 'The default student user must be seeded in the database.');

        // 2. Perform authenticated requests to all student pages
        $pages = [
            'dashboard' => '/dashboard',
            'courses' => '/courses',
            'exams' => '/exams',
            'current-affairs' => '/current-affairs',
            'leaderboard' => '/leaderboard',
            'profile' => '/profile',
            'notifications' => '/notifications',
            'search' => '/search?q=Polity',
        ];

        foreach ($pages as $name => $uri) {
            $response = $this->actingAs($student)->get($uri);
            
            $response->assertStatus(200, "The student page '{$name}' at URI '{$uri}' failed to load with status 200.");
        }
    }

    /**
     * Test starting, taking, and submitting a mock exam.
     */
    public function test_student_can_take_exam(): void
    {
        $student = User::where('email', 'student@thisai.com')->first();
        $this->assertNotNull($student);

        $exam = \App\Models\Exam::where('slug', 'polity-daily-quiz-1')->first();
        $this->assertNotNull($exam);

        // 1. Post to start the exam
        $response = $this->actingAs($student)->post("/exams/{$exam->slug}/start");
        $response->assertRedirect();

        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('/exams/take/', $redirectUrl);

        // 2. Fetch the exam taking page
        $takeResponse = $this->actingAs($student)->get($redirectUrl);
        $takeResponse->assertStatus(200);
        $takeResponse->assertSee($exam->title);

        // 3. Post to submit the exam
        preg_match('#/exams/take/([^/?]+)#', $redirectUrl, $matches);
        $sessionToken = $matches[1] ?? null;
        $this->assertNotNull($sessionToken);

        $submitResponse = $this->actingAs($student)->post("/exams/submit/{$sessionToken}");
        $submitResponse->assertRedirect();
        $submitResponse->assertRedirect(route('results.show', $sessionToken));
    }

    /**
     * Test starting an exam with a passcode/activation key.
     */
    public function test_student_must_enter_passcode_for_protected_exam(): void
    {
        $student = User::where('email', 'student@thisai.com')->first();
        $this->assertNotNull($student);

        $exam = \App\Models\Exam::where('slug', 'polity-daily-quiz-1')->first();
        $this->assertNotNull($exam);
        
        // Protect the exam with an activation key
        $exam->activation_key = 'SECRET123';
        $exam->save();

        // 1. Post to start the exam without passcode -> should redirect to verify page
        $response = $this->actingAs($student)->post("/exams/{$exam->slug}/start");
        $response->assertRedirect(route('exams.verify', $exam->slug));

        // 2. Post to start the exam with incorrect passcode -> should redirect to verify page with error
        $response = $this->actingAs($student)->post("/exams/{$exam->slug}/start", [
            'activation_key' => 'WRONG',
        ]);
        $response->assertRedirect(route('exams.verify', $exam->slug));
        $response->assertSessionHas('error');

        // 3. Post to start the exam with correct passcode -> should succeed and redirect to take page
        $response = $this->actingAs($student)->post("/exams/{$exam->slug}/start", [
            'activation_key' => 'SECRET123',
        ]);
        $response->assertRedirect();
        
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('/exams/take/', $redirectUrl);
    }

    /**
     * Test starting an exam with attempt limits.
     */
    public function test_student_cannot_exceed_max_attempts(): void
    {
        $student = User::where('email', 'student@thisai.com')->first();
        $this->assertNotNull($student);

        $exam = \App\Models\Exam::where('slug', 'polity-daily-quiz-1')->first();
        $this->assertNotNull($exam);

        // Set max attempts to 1
        $exam->max_attempts = 1;
        $exam->save();

        // 1. First attempt: start, submit
        $response = $this->actingAs($student)->post("/exams/{$exam->slug}/start");
        $redirectUrl = $response->headers->get('Location');
        preg_match('#/exams/take/([^/?]+)#', $redirectUrl, $matches);
        $sessionToken = $matches[1] ?? null;
        
        $this->actingAs($student)->post("/exams/submit/{$sessionToken}")->assertRedirect();

        // 2. Second attempt: trying to start again -> should redirect to results or show error message
        $response2 = $this->actingAs($student)->post("/exams/{$exam->slug}/start");
        $response2->assertRedirect(route('results.show', $sessionToken));
        $response2->assertSessionHas('info');
    }

    /**
     * Test that saving answers via AJAX persists them in the database,
     * and submitting the test evaluates the score correctly.
     */
    public function test_student_saves_answers_and_submits(): void
    {
        $student = User::where('email', 'student@thisai.com')->first();
        $this->assertNotNull($student);

        $exam = \App\Models\Exam::where('slug', 'polity-daily-quiz-1')->first();
        $this->assertNotNull($exam);

        // 1. Start the exam
        $response = $this->actingAs($student)->post("/exams/{$exam->slug}/start");
        $response->assertRedirect();
        $redirectUrl = $response->headers->get('Location');
        preg_match('#/exams/take/([^/?]+)#', $redirectUrl, $matches);
        $sessionToken = $matches[1] ?? null;
        $this->assertNotNull($sessionToken);

        $attempt = \App\Models\ExamAttempt::where('session_token', $sessionToken)->first();
        $this->assertNotNull($attempt);

        // 2. For each question, save the correct options via API
        foreach ($exam->questions as $question) {
            $correctOptionIds = $question->options->where('is_correct', true)->pluck('id')->toArray();
            
            $apiResponse = $this->actingAs($student)->postJson('/api/exam/save-answer', [
                'session_token' => $sessionToken,
                'question_id' => $question->id,
                'selected_option_ids' => $correctOptionIds,
                'time_spent_seconds' => 10,
            ]);

            $apiResponse->assertStatus(200);
            $apiResponse->assertJson(['success' => true]);

            // Assert it was saved in the database
            $this->assertDatabaseHas('attempt_answers', [
                'exam_attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'time_spent_seconds' => 10,
            ]);
        }

        // 3. Submit the exam
        $submitResponse = $this->actingAs($student)->post("/exams/submit/{$sessionToken}");
        $submitResponse->assertRedirect(route('results.show', $sessionToken));

        // 4. Verify score calculation is 100% correct
        $attempt->refresh();
        $this->assertEquals(\App\Enums\ExamAttemptStatus::SUBMITTED, $attempt->status);
        $this->assertEquals($exam->questions->count(), $attempt->correct_count);
        $this->assertEquals(0, $attempt->wrong_count);
        $this->assertEquals(0, $attempt->unanswered_count);
        $this->assertEquals(100.00, $attempt->accuracy);
    }
}

