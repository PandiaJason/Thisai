<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CurrentAffairsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

// Redirect welcome page to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Student Portal Routes (Authenticated Students)
Route::middleware(['auth', 'role:student,super_admin,faculty'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Courses
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');
    Route::post('/courses/{course:slug}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');

    // Videos
    Route::get('/videos/{video}', [VideoController::class, 'watch'])->name('videos.watch');

    // Exams & Tests
    Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/{exam:slug}/verify', [ExamController::class, 'verify'])->name('exams.verify');
    Route::post('/exams/{exam:slug}/start', [ExamController::class, 'start'])->name('exams.start');
    Route::get('/exams/take/{session_token}', [ExamController::class, 'take'])->name('exams.take');
    Route::post('/exams/submit/{session_token}', [ExamController::class, 'submit'])->name('exams.submit');
    Route::get('/results/{session_token}', [ResultController::class, 'show'])->name('results.show');

    // Current Affairs
    Route::get('/current-affairs', [CurrentAffairsController::class, 'index'])->name('current-affairs.index');
    Route::get('/current-affairs/{article:slug}', [CurrentAffairsController::class, 'show'])->name('current-affairs.show');

    // Leaderboard
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');

    // Global Search
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // Bookmarks
    Route::post('/bookmarks/toggle', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // AJAX and API routes using web sessions
    Route::post('/api/video/progress', [App\Http\Controllers\Api\VideoProgressController::class, 'store']);
    Route::post('/api/exam/save-answer', [App\Http\Controllers\Api\ExamApiController::class, 'saveAnswer']);
    Route::get('/api/exam/time-remaining', [App\Http\Controllers\Api\ExamApiController::class, 'getTimeRemaining']);
});
