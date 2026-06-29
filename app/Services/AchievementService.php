<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;
use App\Models\UserBadge;
use App\Models\ExamAttempt;
use App\Enums\ExamAttemptStatus;
use Carbon\Carbon;

class AchievementService
{
    /**
     * Track study activity and update streaks.
     */
    public function trackActivity(User $user): void
    {
        $profile = $user->studentProfile;
        if (!$profile) {
            return;
        }

        $today = Carbon::today();
        $lastActivity = $profile->last_activity_date ? Carbon::parse($profile->last_activity_date) : null;

        if ($lastActivity === null) {
            $profile->current_streak = 1;
            $profile->highest_streak = max($profile->highest_streak, 1);
        } elseif ($lastActivity->isToday()) {
            // Already active today, do not increment but don't reset
        } elseif ($lastActivity->isYesterday()) {
            // Consecutive day
            $profile->current_streak += 1;
            $profile->highest_streak = max($profile->highest_streak, $profile->current_streak);
        } else {
            // Skipped a day, reset streak
            $profile->current_streak = 1;
        }

        $profile->last_activity_date = $today;
        $profile->save();

        // Re-evaluate streak-related badges
        $this->evaluateBadges($user);
    }

    /**
     * Evaluate and unlock achievements.
     */
    public function evaluateBadges(User $user): array
    {
        $unlocked = [];
        $profile = $user->studentProfile;
        if (!$profile) {
            return [];
        }

        $badges = Badge::all();
        $alreadyUnlocked = $user->badges()->pluck('badges.id')->toArray();

        foreach ($badges as $badge) {
            if (in_array($badge->id, $alreadyUnlocked)) {
                continue;
            }

            $shouldUnlock = false;
            $criteria = $badge->criteria_value;

            switch ($badge->criteria_type) {
                case 'streak_king':
                    $requiredStreak = $criteria['streak'] ?? 7;
                    if ($profile->highest_streak >= $requiredStreak) {
                        $shouldUnlock = true;
                    }
                    break;

                case 'accuracy_ace':
                    $minAccuracy = $criteria['accuracy'] ?? 95;
                    $hasAccurateAttempt = ExamAttempt::where('user_id', $user->id)
                        ->where('status', ExamAttemptStatus::SUBMITTED->value)
                        ->where('accuracy', '>=', $minAccuracy)
                        ->exists();
                    if ($hasAccurateAttempt) {
                        $shouldUnlock = true;
                    }
                    break;

                case 'subject_mastery':
                    $subjectId = $criteria['subject_id'] ?? null;
                    $minAccuracy = $criteria['accuracy'] ?? 90;
                    $minCount = $criteria['count'] ?? 3;

                    if ($subjectId) {
                        $count = ExamAttempt::where('user_id', $user->id)
                            ->where('status', ExamAttemptStatus::SUBMITTED->value)
                            ->where('accuracy', '>=', $minAccuracy)
                            ->whereHas('exam', function ($q) use ($subjectId) {
                                $q->where('subject_id', $subjectId);
                            })
                            ->count();
                        if ($count >= $minCount) {
                            $shouldUnlock = true;
                        }
                    }
                    break;

                case 'daily_reader':
                    $requiredDays = $criteria['days'] ?? 5;
                    if ($profile->highest_streak >= $requiredDays) {
                        $shouldUnlock = true;
                    }
                    break;
            }

            if ($shouldUnlock) {
                $user->badges()->attach($badge->id, ['unlocked_at' => Carbon::now()]);
                $unlocked[] = $badge;
            }
        }

        return $unlocked;
    }
}
