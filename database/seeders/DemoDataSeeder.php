<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subject;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Video;
use App\Models\CurrentAffairs;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Option;
use App\Enums\UserRole;
use App\Enums\ExamType;
use App\Enums\QuestionType;
use App\Enums\CourseDifficulty;
use App\Enums\CourseStatus;
use App\Enums\CurrentAffairsType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get seed data users
        $admin = User::where('role', UserRole::SUPER_ADMIN)->first();
        $faculty = User::where('role', UserRole::FACULTY)->first();
        
        if (!$admin || !$faculty) {
            echo "Base users (Admin/Faculty) not found. Please run DatabaseSeeder first.\n";
            return;
        }

        // Get subjects
        $polity = Subject::where('name', 'Polity')->first();
        $history = Subject::where('name', 'History')->first();
        $economics = Subject::where('name', 'Economics')->first();
        $geography = Subject::where('name', 'Geography')->first();
        $currentAffairsSub = Subject::where('name', 'Current Affairs')->first();

        // 2. Create Courses
        $polityCourse = Course::updateOrCreate(
            ['slug' => 'mastering-upsc-indian-polity'],
            [
                'title' => 'Mastering UPSC Indian Polity',
                'description' => 'Comprehensive course covering the entire syllabus of Indian Polity & Governance for UPSC CSE Prelims & Mains.',
                'thumbnail' => 'courses/polity.jpg',
                'subject_id' => $polity->id,
                'faculty_id' => $faculty->id,
                'status' => CourseStatus::PUBLISHED,
                'difficulty' => CourseDifficulty::BEGINNER,
                'duration_hours' => 45,
                'is_free' => true,
                'price' => 0.00,
                'sort_order' => 1,
            ]
        );

        $econCourse = Course::updateOrCreate(
            ['slug' => 'upsc-economy-core-concepts'],
            [
                'title' => 'UPSC Economy Core Concepts',
                'description' => 'Master the foundational concepts of Macroeconomics, Inflation, Banking, Budget, and Fiscal Policy.',
                'thumbnail' => 'courses/economy.jpg',
                'subject_id' => $economics->id,
                'faculty_id' => $faculty->id,
                'status' => CourseStatus::PUBLISHED,
                'difficulty' => CourseDifficulty::INTERMEDIATE,
                'duration_hours' => 30,
                'is_free' => false,
                'price' => 4999.00,
                'sort_order' => 2,
            ]
        );

        // Sections
        $sec1 = CourseSection::updateOrCreate(
            ['course_id' => $polityCourse->id, 'title' => 'Introduction & Historical Background'],
            ['sort_order' => 1]
        );

        $sec2 = CourseSection::updateOrCreate(
            ['course_id' => $polityCourse->id, 'title' => 'Fundamental Rights & Duties'],
            ['sort_order' => 2]
        );

        $sec3 = CourseSection::updateOrCreate(
            ['course_id' => $econCourse->id, 'title' => 'Introduction to Macroeconomics & National Income'],
            ['sort_order' => 1]
        );

        // Videos
        Video::updateOrCreate(
            ['bunny_video_id' => 'demo-vid-1'],
            [
                'title' => 'Historical Background: Regulating Act of 1773 to Charter Acts',
                'description' => 'Detailed lecture covering the evolution of the Indian Constitution through early British legislation.',
                'course_section_id' => $sec1->id,
                'course_id' => $polityCourse->id,
                'subject_id' => $polity->id,
                'uploaded_by' => $faculty->id,
                'bunny_library_id' => '12345',
                'duration_seconds' => 1800, // 30 minutes
                'thumbnail_url' => 'videos/thumbnails/polity-1.jpg',
                'status' => 'ready',
                'is_free' => true,
                'sort_order' => 1,
            ]
        );

        Video::updateOrCreate(
            ['bunny_video_id' => 'demo-vid-2'],
            [
                'title' => 'Fundamental Rights: Article 14 to 18 (Right to Equality)',
                'description' => 'Analysing the Core fundamental rights guaranteeing equality, landmarks cases, and constitutional exceptions.',
                'course_section_id' => $sec2->id,
                'course_id' => $polityCourse->id,
                'subject_id' => $polity->id,
                'uploaded_by' => $faculty->id,
                'bunny_library_id' => '12345',
                'duration_seconds' => 2400, // 40 minutes
                'thumbnail_url' => 'videos/thumbnails/polity-2.jpg',
                'status' => 'ready',
                'is_free' => true,
                'sort_order' => 1,
            ]
        );

        Video::updateOrCreate(
            ['bunny_video_id' => 'demo-vid-3'],
            [
                'title' => 'National Income Accounting: GDP, GNP, NDP, NNP',
                'description' => 'Understand the fundamental measures of national income and production used globally and in India.',
                'course_section_id' => $sec3->id,
                'course_id' => $econCourse->id,
                'subject_id' => $economics->id,
                'uploaded_by' => $faculty->id,
                'bunny_library_id' => '12345',
                'duration_seconds' => 2100, // 35 minutes
                'thumbnail_url' => 'videos/thumbnails/econ-1.jpg',
                'status' => 'ready',
                'is_free' => false,
                'sort_order' => 1,
            ]
        );

        // 3. Create Current Affairs
        CurrentAffairs::updateOrCreate(
            ['slug' => 'governor-role-in-bill-assent-supreme-court-rulings'],
            [
                'title' => 'Governor\'s Role in Bill Assent: Supreme Court Rulings',
                'content' => '<p>The role of the state Governor in giving assent to bills passed by state legislatures has come under judicial scrutiny. The Supreme Court has reiterated that under Article 200 of the Constitution, the Governor cannot sit indefinitely on bills passed by the legislature.</p><h4>Key Provisions:</h4><ul><li><strong>Article 200:</strong> Deals with options available to the Governor when a bill is presented (assent, withhold, reserve for President, return for reconsideration).</li><li><strong>Article 201:</strong> Bill reserved for President\'s consideration.</li></ul><p>Judicial precedent establishes that "as soon as possible" in Article 200 requires prompt action, failing which the democratic legislative processes are undermined.</p>',
                'author_id' => $faculty->id,
                'subject_id' => $polity->id,
                'type' => CurrentAffairsType::EDITORIAL,
                'publish_date' => now()->toDateString(),
                'is_published' => true,
                'tags' => ['Governor', 'Article 200', 'Supreme Court', 'Federalism'],
            ]
        );

        CurrentAffairs::updateOrCreate(
            ['slug' => 'understanding-capital-adequacy-ratio-car-in-banking'],
            [
                'title' => 'Understanding Capital Adequacy Ratio (CAR) in Banking',
                'content' => '<p>The Capital Adequacy Ratio (CAR) is a key measurement of a bank\'s available capital expressed as a percentage of its risk-weighted credit exposure. It is also known as capital-to-risk weighted assets ratio (CRAR).</p><h4>Significance:</h4><ul><li>Prevents banks from taking excess leverage and becoming insolvent in a crisis.</li><li>Protects depositors\' funds and maintains financial stability.</li><li>Under Basel III norms, the minimum CAR requirement is 8% (9% as mandated by RBI in India).</li></ul>',
                'author_id' => $faculty->id,
                'subject_id' => $economics->id,
                'type' => CurrentAffairsType::DAILY,
                'publish_date' => now()->toDateString(),
                'is_published' => true,
                'tags' => ['Banking', 'CAR', 'Basel III', 'RBI'],
            ]
        );

        // 4. Create Exams
        // Daily Quiz
        $quiz = Exam::updateOrCreate(
            ['slug' => 'polity-daily-quiz-1'],
            [
                'title' => 'Polity Daily Quiz - 1',
                'description' => 'Test your daily preparation on basic features of the Indian Constitution and Preamble.',
                'subject_id' => $polity->id,
                'created_by' => $faculty->id,
                'type' => ExamType::DAILY_QUIZ,
                'difficulty' => 'easy',
                'duration_minutes' => 5,
                'total_marks' => 10,
                'negative_marking' => 0.33,
                'randomize_questions' => true,
                'randomize_options' => true,
                'is_published' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addYear(),
            ]
        );

        // Section Test
        $sectionTest = Exam::updateOrCreate(
            ['slug' => 'modern-history-section-test-1'],
            [
                'title' => 'Modern History Section Test',
                'description' => 'Comprehensive test covering the Struggle for Independence: 1857 to 1919.',
                'subject_id' => $history->id,
                'created_by' => $faculty->id,
                'type' => ExamType::SECTION_TEST,
                'difficulty' => 'medium',
                'duration_minutes' => 15,
                'total_marks' => 20,
                'negative_marking' => 0.33,
                'randomize_questions' => false,
                'randomize_options' => true,
                'is_published' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addYear(),
            ]
        );

        // Full Mock Test
        $mockTest = Exam::updateOrCreate(
            ['slug' => 'upsc-cse-prelims-mock-test-1'],
            [
                'title' => 'UPSC CSE Prelims Mock Test - 1',
                'description' => 'Full length mock test covering GS Paper-I syllabus including Polity, History, Economy, Geography, and Environment.',
                'subject_id' => $currentAffairsSub->id,
                'created_by' => $faculty->id,
                'type' => ExamType::MOCK_TEST,
                'difficulty' => 'hard',
                'duration_minutes' => 30,
                'total_marks' => 40,
                'negative_marking' => 0.67,
                'randomize_questions' => true,
                'randomize_options' => false,
                'is_published' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addYear(),
            ]
        );

        // 5. Add Questions & Options for Polity Daily Quiz (5 Questions)
        $q1 = Question::create([
            'exam_id' => $quiz->id,
            'subject_id' => $polity->id,
            'question_text' => 'Which of the following describes India as a "Secular State"?',
            'explanation' => 'The Preamble of the Constitution of India describes India as a "Sovereign, Socialist, Secular, Democratic, Republic". It guarantees liberty of belief, faith and worship.',
            'type' => QuestionType::SINGLE_CORRECT,
            'difficulty' => 'easy',
            'marks' => 2,
            'negative_marks' => 0.66,
            'sort_order' => 1,
        ]);
        Option::create(['question_id' => $q1->id, 'option_text' => 'Directive Principles of State Policy', 'is_correct' => false, 'sort_order' => 1]);
        Option::create(['question_id' => $q1->id, 'option_text' => 'Preamble of the Constitution', 'is_correct' => true, 'sort_order' => 2]);
        Option::create(['question_id' => $q1->id, 'option_text' => 'Union List under Seventh Schedule', 'is_correct' => false, 'sort_order' => 3]);
        Option::create(['question_id' => $q1->id, 'option_text' => 'Fundamental Rights', 'is_correct' => false, 'sort_order' => 4]);

        $q2 = Question::create([
            'exam_id' => $quiz->id,
            'subject_id' => $polity->id,
            'question_text' => 'The concept of "Directive Principles of State Policy" in the Indian Constitution is borrowed from the constitution of:',
            'explanation' => 'The Directive Principles of State Policy (Part IV) were borrowed from the Constitution of Ireland, which had copied it from the Spanish Constitution.',
            'type' => QuestionType::SINGLE_CORRECT,
            'difficulty' => 'easy',
            'marks' => 2,
            'negative_marks' => 0.66,
            'sort_order' => 2,
        ]);
        Option::create(['question_id' => $q2->id, 'option_text' => 'USA', 'is_correct' => false, 'sort_order' => 1]);
        Option::create(['question_id' => $q2->id, 'option_text' => 'Ireland', 'is_correct' => true, 'sort_order' => 2]);
        Option::create(['question_id' => $q2->id, 'option_text' => 'USSR', 'is_correct' => false, 'sort_order' => 3]);
        Option::create(['question_id' => $q2->id, 'option_text' => 'Australia', 'is_correct' => false, 'sort_order' => 4]);

        $q3 = Question::create([
            'exam_id' => $quiz->id,
            'subject_id' => $polity->id,
            'question_text' => 'Which article of the Indian Constitution guarantees the "Right to Constitutional Remedies"?',
            'explanation' => 'Article 32 guarantees the Right to Constitutional Remedies, which allows citizens to approach the Supreme Court for enforcement of their Fundamental Rights. Dr. B.R. Ambedkar called Article 32 the "Heart and Soul" of the Constitution.',
            'type' => QuestionType::SINGLE_CORRECT,
            'difficulty' => 'easy',
            'marks' => 2,
            'negative_marks' => 0.66,
            'sort_order' => 3,
        ]);
        Option::create(['question_id' => $q3->id, 'option_text' => 'Article 19', 'is_correct' => false, 'sort_order' => 1]);
        Option::create(['question_id' => $q3->id, 'option_text' => 'Article 21', 'is_correct' => false, 'sort_order' => 2]);
        Option::create(['question_id' => $q3->id, 'option_text' => 'Article 32', 'is_correct' => true, 'sort_order' => 3]);
        Option::create(['question_id' => $q3->id, 'option_text' => 'Article 226', 'is_correct' => false, 'sort_order' => 4]);

        $q4 = Question::create([
            'exam_id' => $quiz->id,
            'subject_id' => $polity->id,
            'question_text' => 'Fundamental Duties were incorporated into the Constitution of India upon the recommendation of which Committee?',
            'explanation' => 'The Fundamental Duties of citizens were added to the Constitution by the 42nd Amendment in 1976, upon the recommendation of the Swaran Singh Committee.',
            'type' => QuestionType::SINGLE_CORRECT,
            'difficulty' => 'easy',
            'marks' => 2,
            'negative_marks' => 0.66,
            'sort_order' => 4,
        ]);
        Option::create(['question_id' => $q4->id, 'option_text' => 'Sarkaria Commission', 'is_correct' => false, 'sort_order' => 1]);
        Option::create(['question_id' => $q4->id, 'option_text' => 'Balwant Rai Mehta Committee', 'is_correct' => false, 'sort_order' => 2]);
        Option::create(['question_id' => $q4->id, 'option_text' => 'Swaran Singh Committee', 'is_correct' => true, 'sort_order' => 3]);
        Option::create(['question_id' => $q4->id, 'option_text' => 'Verma Committee', 'is_correct' => false, 'sort_order' => 4]);

        $q5 = Question::create([
            'exam_id' => $quiz->id,
            'subject_id' => $polity->id,
            'question_text' => 'The Preamble to the Indian Constitution has been amended how many times?',
            'explanation' => 'The Preamble has been amended only once so far, by the 42nd Constitutional Amendment Act of 1976, which added three new words: Socialist, Secular, and Integrity.',
            'type' => QuestionType::SINGLE_CORRECT,
            'difficulty' => 'easy',
            'marks' => 2,
            'negative_marks' => 0.66,
            'sort_order' => 5,
        ]);
        Option::create(['question_id' => $q5->id, 'option_text' => 'Once', 'is_correct' => true, 'sort_order' => 1]);
        Option::create(['question_id' => $q5->id, 'option_text' => 'Twice', 'is_correct' => false, 'sort_order' => 2]);
        Option::create(['question_id' => $q5->id, 'option_text' => 'Thrice', 'is_correct' => false, 'sort_order' => 3]);
        Option::create(['question_id' => $q5->id, 'option_text' => 'Never', 'is_correct' => false, 'sort_order' => 4]);


        // 6. Add Questions & Options for Modern History Section Test (10 Questions)
        $hq1 = Question::create([
            'exam_id' => $sectionTest->id,
            'subject_id' => $history->id,
            'question_text' => 'Who was the Governor-General of India during the Revolt of 1857?',
            'explanation' => 'Lord Canning was the Governor-General of India during the Revolt of 1857. He became the first Viceroy of India under the Government of India Act 1858.',
            'type' => QuestionType::SINGLE_CORRECT,
            'difficulty' => 'medium',
            'marks' => 2,
            'negative_marks' => 0.66,
            'sort_order' => 1,
        ]);
        Option::create(['question_id' => $hq1->id, 'option_text' => 'Lord Dalhousie', 'is_correct' => false, 'sort_order' => 1]);
        Option::create(['question_id' => $hq1->id, 'option_text' => 'Lord Canning', 'is_correct' => true, 'sort_order' => 2]);
        Option::create(['question_id' => $hq1->id, 'option_text' => 'Lord Elgin', 'is_correct' => false, 'sort_order' => 3]);
        Option::create(['question_id' => $hq1->id, 'option_text' => 'Lord Lytton', 'is_correct' => false, 'sort_order' => 4]);

        $hq2 = Question::create([
            'exam_id' => $sectionTest->id,
            'subject_id' => $history->id,
            'question_text' => 'Which partition of Bengal was carried out by Lord Curzon?',
            'explanation' => 'Lord Curzon partitioned Bengal in 1905 to suppress national consciousness. It was later reunited/annulled in 1911 by Lord Hardinge.',
            'type' => QuestionType::SINGLE_CORRECT,
            'difficulty' => 'medium',
            'marks' => 2,
            'negative_marks' => 0.66,
            'sort_order' => 2,
        ]);
        Option::create(['question_id' => $hq2->id, 'option_text' => '1903', 'is_correct' => false, 'sort_order' => 1]);
        Option::create(['question_id' => $hq2->id, 'option_text' => '1905', 'is_correct' => true, 'sort_order' => 2]);
        Option::create(['question_id' => $hq2->id, 'option_text' => '1909', 'is_correct' => false, 'sort_order' => 3]);
        Option::create(['question_id' => $hq2->id, 'option_text' => '1911', 'is_correct' => false, 'sort_order' => 4]);

        $hq3 = Question::create([
            'exam_id' => $sectionTest->id,
            'subject_id' => $history->id,
            'question_text' => 'In which year was the Non-Cooperation Movement launched by Mahatma Gandhi?',
            'explanation' => 'The Non-Cooperation Movement was launched in August 1920 to protest against the Rowlatt Act, the Jallianwala Bagh massacre, and the Khilafat issue.',
            'type' => QuestionType::SINGLE_CORRECT,
            'difficulty' => 'medium',
            'marks' => 2,
            'negative_marks' => 0.66,
            'sort_order' => 3,
        ]);
        Option::create(['question_id' => $hq3->id, 'option_text' => '1918', 'is_correct' => false, 'sort_order' => 1]);
        Option::create(['question_id' => $hq3->id, 'option_text' => '1919', 'is_correct' => false, 'sort_order' => 2]);
        Option::create(['question_id' => $hq3->id, 'option_text' => '1920', 'is_correct' => true, 'sort_order' => 3]);
        Option::create(['question_id' => $hq3->id, 'option_text' => '1922', 'is_correct' => false, 'sort_order' => 4]);

        // Mock remaining questions to keep it structured and fast but complete
        for ($i = 4; $i <= 10; $i++) {
            $q = Question::create([
                'exam_id' => $sectionTest->id,
                'subject_id' => $history->id,
                'question_text' => "Modern History Demo Question {$i}: Which of the following is associated with the Swadeshi Movement?",
                'explanation' => 'The Swadeshi Movement arose from the anti-partition agitation of Bengal in 1905, emphasizing boycott of foreign goods and promotion of national education and industries.',
                'type' => QuestionType::SINGLE_CORRECT,
                'difficulty' => 'medium',
                'marks' => 2,
                'negative_marks' => 0.66,
                'sort_order' => $i,
            ]);
            Option::create(['question_id' => $q->id, 'option_text' => 'Boycott of foreign textiles', 'is_correct' => true, 'sort_order' => 1]);
            Option::create(['question_id' => $q->id, 'option_text' => 'Active support for British institutions', 'is_correct' => false, 'sort_order' => 2]);
            Option::create(['question_id' => $q->id, 'option_text' => 'Signing of Versailles Treaty', 'is_correct' => false, 'sort_order' => 3]);
            Option::create(['question_id' => $q->id, 'option_text' => 'Establishment of East India Association', 'is_correct' => false, 'sort_order' => 4]);
        }

        // 7. Add Questions for Full Mock Test (10 Questions)
        for ($i = 1; $i <= 10; $i++) {
            $q = Question::create([
                'exam_id' => $mockTest->id,
                'subject_id' => $geography->id,
                'question_text' => "General Studies Full Mock Question {$i}: Which of the following rivers originates from the Tibetan Plateau?",
                'explanation' => 'The Brahmaputra (known as Yarlung Tsangpo in Tibet), Indus, and Sutlej rivers originate in the Tibetan Plateau near Lake Mansarovar.',
                'type' => QuestionType::SINGLE_CORRECT,
                'difficulty' => 'hard',
                'marks' => 4,
                'negative_marks' => 1.33,
                'sort_order' => $i,
            ]);
            Option::create(['question_id' => $q->id, 'option_text' => 'Brahmaputra', 'is_correct' => true, 'sort_order' => 1]);
            Option::create(['question_id' => $q->id, 'option_text' => 'Ganga', 'is_correct' => false, 'sort_order' => 2]);
            Option::create(['question_id' => $q->id, 'option_text' => 'Godavari', 'is_correct' => false, 'sort_order' => 3]);
            Option::create(['question_id' => $q->id, 'option_text' => 'Narmada', 'is_correct' => false, 'sort_order' => 4]);
        }
        
        echo "Successfully seeded demo courses, sections, videos, current affairs, and mock test exams.\n";
    }
}
