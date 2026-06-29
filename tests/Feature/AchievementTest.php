<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\User;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Category;
use App\Models\StudentProfile;
use App\Models\ExamAttempt;
use App\Models\AttemptAnswer;
use App\Models\Option;
use App\Models\Question;
use App\Enums\UserRole;
use App\Enums\ExamAttemptStatus;
use App\Services\AchievementService;
use Carbon\Carbon;
use Database\Seeders\BadgeSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected AchievementService $achievementService;
    protected User $student;
    protected StudentProfile $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->achievementService = new AchievementService();

        // Create student
        $this->student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'is_active' => true,
        ]);

        $this->profile = StudentProfile::create([
            'user_id' => $this->student->id,
            'current_streak' => 0,
            'highest_streak' => 0,
            'last_activity_date' => null,
        ]);
    }

    /** @test */
    public function test_it_increments_streak_on_consecutive_days()
    {
        // Day 1
        Carbon::setTestNow(Carbon::today());
        $this->achievementService->trackActivity($this->student);

        $this->profile->refresh();
        $this->assertEquals(1, $this->profile->current_streak);
        $this->assertEquals(1, $this->profile->highest_streak);

        // Day 2 (Consecutive)
        Carbon::setTestNow(Carbon::tomorrow());
        $this->achievementService->trackActivity($this->student);

        $this->profile->refresh();
        $this->assertEquals(2, $this->profile->current_streak);
        $this->assertEquals(2, $this->profile->highest_streak);
    }

    /** @test */
    public function test_it_does_not_increment_streak_on_multiple_activities_same_day()
    {
        Carbon::setTestNow(Carbon::today());
        
        $this->achievementService->trackActivity($this->student);
        $this->achievementService->trackActivity($this->student);

        $this->profile->refresh();
        $this->assertEquals(1, $this->profile->current_streak);
    }

    /** @test */
    public function test_it_resets_streak_if_a_day_is_skipped()
    {
        // Day 1
        Carbon::setTestNow(Carbon::today());
        $this->achievementService->trackActivity($this->student);

        // Day 3 (Skipped Day 2)
        Carbon::setTestNow(Carbon::today()->addDays(2));
        $this->achievementService->trackActivity($this->student);

        $this->profile->refresh();
        $this->assertEquals(1, $this->profile->current_streak);
        $this->assertEquals(1, $this->profile->highest_streak); // Highest streak remains 1
    }

    /** @test */
    public function test_it_unlocks_accuracy_ace_badge_on_high_score()
    {
        $exam = Exam::create([
            'title' => 'Polity Test',
            'slug' => 'polity-test',
            'type' => 'daily_quiz',
            'duration_minutes' => 10,
            'total_marks' => 10,
            'is_published' => true,
            'created_by' => $this->student->id,
        ]);

        ExamAttempt::create([
            'user_id' => $this->student->id,
            'exam_id' => $exam->id,
            'started_at' => Carbon::now(),
            'submitted_at' => Carbon::now(),
            'status' => ExamAttemptStatus::SUBMITTED,
            'accuracy' => 96.00,
            'score' => 9,
            'session_token' => 'token123',
        ]);

        // Evaluate achievements
        $unlocked = $this->achievementService->evaluateBadges($this->student);

        $this->assertCount(1, $unlocked);
        $this->assertEquals('accuracy-ace', $unlocked[0]->slug);
        $this->assertTrue($this->student->badges()->where('slug', 'accuracy-ace')->exists());
    }

    /** @test */
    public function test_it_unlocks_subject_mastery_badge_after_three_attempts()
    {
        $subject = Subject::where('name', 'Polity')->first();

        $exam = Exam::create([
            'title' => 'Polity Quiz',
            'slug' => 'polity-quiz',
            'subject_id' => $subject->id,
            'type' => 'daily_quiz',
            'duration_minutes' => 10,
            'total_marks' => 10,
            'is_published' => true,
            'created_by' => $this->student->id,
        ]);

        // Create 3 attempts with 90%+ accuracy on Polity
        for ($i = 1; $i <= 3; $i++) {
            ExamAttempt::create([
                'user_id' => $this->student->id,
                'exam_id' => $exam->id,
                'started_at' => Carbon::now(),
                'submitted_at' => Carbon::now(),
                'status' => ExamAttemptStatus::SUBMITTED,
                'accuracy' => 90.00,
                'score' => 9,
                'session_token' => "token{$i}",
            ]);
        }

        $unlocked = $this->achievementService->evaluateBadges($this->student);

        // Should unlock accuracy-ace (since 90% is below 95% threshold for accuracy-ace, it will only unlock polity-pundit!)
        $this->assertTrue($this->student->badges()->where('slug', 'polity-pundit')->exists());
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
}
