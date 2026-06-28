import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Counter, Rate, Trend } from 'k6/metrics';

// ─── Custom Metrics ──────────────────────────────────────────────────────────
const examStartDuration = new Trend('exam_start_duration', true);
const answerSaveDuration = new Trend('answer_save_duration', true);
const examSubmitDuration = new Trend('exam_submit_duration', true);
const successRate = new Rate('success_rate');
const errorCounter = new Counter('errors');

// ─── Configuration ───────────────────────────────────────────────────────────
const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';
const EXAM_SLUG = __ENV.EXAM_SLUG || 'polity-daily-quiz-1';
const TOTAL_VUS = parseInt(__ENV.TOTAL_VUS || '100');

/**
 * Load test stages:
 *
 * Stage 1 (Ramp-up):     0 → target VUs over 30s   (gradual login + start)
 * Stage 2 (Sustained):   Hold target VUs for 2m     (save answers continuously)
 * Stage 3 (Spike):       Burst submit all at once   (submission spike)
 * Stage 4 (Cool-down):   Ramp down over 10s
 */
export const options = {
    stages: [
        { duration: '30s', target: TOTAL_VUS },     // Ramp up
        { duration: '2m',  target: TOTAL_VUS },     // Sustained load
        { duration: '10s', target: 0 },              // Cool down
    ],
    thresholds: {
        http_req_duration: ['p(95)<2000'],           // 95% of requests < 2s
        http_req_failed:   ['rate<0.05'],            // < 5% error rate
        success_rate:      ['rate>0.90'],            // > 90% success
        exam_start_duration: ['p(95)<3000'],
        answer_save_duration: ['p(95)<1000'],
        exam_submit_duration: ['p(95)<5000'],
    },
};

// ─── Helper: Extract CSRF Token ──────────────────────────────────────────────
function getCsrfToken(html) {
    const match = html.match(/name="csrf-token"\s+content="([^"]+)"/);
    return match ? match[1] : null;
}

function getCSRFFromForm(html) {
    const match = html.match(/name="_token"\s+(?:type="hidden"\s+)?value="([^"]+)"/);
    if (match) return match[1];
    // Try reverse order
    const match2 = html.match(/value="([^"]+)"\s+name="_token"/);
    return match2 ? match2[1] : null;
}

// ─── Main Test Function ──────────────────────────────────────────────────────
export default function () {
    // Each VU gets a unique student ID (1-indexed, wrapping around 3000)
    const studentId = ((__VU - 1) % 10000) + 1;
    const paddedId = String(studentId).padStart(4, '0');
    const email = `loadtest_${paddedId}@thisai.com`;
    const password = 'password';

    let csrfToken = null;
    let sessionToken = null;
    let questions = [];

    // ─── Phase 1: Login ──────────────────────────────────────────────────
    group('01_Login', function () {
        // GET login page to extract CSRF token
        const loginPage = http.get(`${BASE_URL}/login`, {
            redirects: 0,
            tags: { name: 'GET /login' },
        });

        csrfToken = getCSRFFromForm(loginPage.body) || getCsrfToken(loginPage.body);

        if (!csrfToken) {
            errorCounter.add(1);
            successRate.add(false);
            console.error(`VU ${__VU}: Could not extract CSRF token from login page`);
            return;
        }

        // POST login credentials
        const loginRes = http.post(`${BASE_URL}/login`, {
            email: email,
            password: password,
            _token: csrfToken,
        }, {
            redirects: 5,
            tags: { name: 'POST /login' },
        });

        const loginOk = check(loginRes, {
            'login redirects to dashboard': (r) => r.status === 200 || r.url.includes('/dashboard'),
        });

        if (!loginOk) {
            errorCounter.add(1);
            successRate.add(false);
            console.error(`VU ${__VU} (${email}): Login failed - status ${loginRes.status}`);
            return;
        }

        successRate.add(true);

        // Update CSRF token from the dashboard page
        csrfToken = getCsrfToken(loginRes.body) || csrfToken;
    });

    if (!csrfToken) return;

    sleep(Math.random() * 2 + 0.5); // Stagger exam starts (0.5-2.5s)

    // ─── Phase 2: Start Exam ─────────────────────────────────────────────
    group('02_Start_Exam', function () {
        const startTime = Date.now();

        const startRes = http.post(`${BASE_URL}/exams/${EXAM_SLUG}/start`, {
            _token: csrfToken,
        }, {
            redirects: 5,
            tags: { name: 'POST /exams/start' },
        });

        examStartDuration.add(Date.now() - startTime);

        // Extract session token from redirect URL
        const urlMatch = startRes.url.match(/\/exams\/take\/([a-zA-Z0-9]+)/);
        if (urlMatch) {
            sessionToken = urlMatch[1];
        }

        const startOk = check(startRes, {
            'exam started successfully': (r) => r.status === 200 && sessionToken !== null,
        });

        if (!startOk) {
            errorCounter.add(1);
            successRate.add(false);
            console.error(`VU ${__VU}: Exam start failed - status ${startRes.status}, url: ${startRes.url}`);
            return;
        }

        successRate.add(true);

        // Update CSRF token from the exam page
        csrfToken = getCsrfToken(startRes.body) || csrfToken;

        // Extract question IDs from the page (look for question_id in the JSON data)
        const questionsMatch = startRes.body.match(/questions:\s*(\[[\s\S]*?\])\s*,\s*answers:/);
        if (questionsMatch) {
            try {
                questions = JSON.parse(questionsMatch[1]);
            } catch (e) {
                // Fallback: just use dummy question IDs
                console.warn(`VU ${__VU}: Could not parse questions JSON`);
            }
        }
    });

    if (!sessionToken) return;

    // ─── Phase 3: Save Answers (Simulates student answering questions) ───
    group('03_Save_Answers', function () {
        const numQuestions = questions.length || 5; // Fallback to 5 if parsing failed

        for (let i = 0; i < numQuestions; i++) {
            const q = questions[i];
            if (!q) continue;

            // Pick the first option as the answer (simulated random choice)
            const selectedOptionId = q.options && q.options.length > 0 ? q.options[0].id : 1;

            const saveTime = Date.now();

            const saveRes = http.post(`${BASE_URL}/api/exam/save-answer`,
                JSON.stringify({
                    session_token: sessionToken,
                    question_id: q.id,
                    selected_option_ids: [selectedOptionId],
                    time_spent_seconds: Math.floor(Math.random() * 30) + 5,
                }),
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    tags: { name: 'POST /api/exam/save-answer' },
                }
            );

            answerSaveDuration.add(Date.now() - saveTime);

            const saveOk = check(saveRes, {
                'answer saved': (r) => r.status === 200,
            });

            if (!saveOk) {
                errorCounter.add(1);
                successRate.add(false);
            } else {
                successRate.add(true);
            }

            // Simulate student thinking time (1-5 seconds between questions)
            sleep(Math.random() * 4 + 1);
        }
    });

    // ─── Phase 4: Submit Exam ────────────────────────────────────────────
    group('04_Submit_Exam', function () {
        const submitTime = Date.now();

        const submitRes = http.post(`${BASE_URL}/exams/submit/${sessionToken}`, {
            _token: csrfToken,
        }, {
            redirects: 5,
            tags: { name: 'POST /exams/submit' },
        });

        examSubmitDuration.add(Date.now() - submitTime);

        const submitOk = check(submitRes, {
            'exam submitted': (r) => r.status === 200 || r.url.includes('/results/'),
        });

        if (!submitOk) {
            errorCounter.add(1);
            successRate.add(false);
            console.error(`VU ${__VU}: Submit failed - status ${submitRes.status}`);
        } else {
            successRate.add(true);
        }
    });

    // ─── Phase 5: Logout ─────────────────────────────────────────────────
    group('05_Logout', function () {
        http.post(`${BASE_URL}/logout`, {
            _token: csrfToken,
        }, {
            redirects: 5,
            tags: { name: 'POST /logout' },
        });
    });
}

// ─── Summary Reporter ────────────────────────────────────────────────────────
export function handleSummary(data) {
    const getMetricVal = (metric, name, suffix = 'ms') => {
        if (!metric || !metric.values) return 'N/A';
        const val = metric.values[name];
        if (val === undefined || val === null) return 'N/A';
        return `${val.toFixed(0)}${suffix}`;
    };

    const summary = {
        '--- THISAI Load Test Results ---': '',
        'Total Requests': data.metrics.http_reqs ? data.metrics.http_reqs.values.count : 0,
        'Failed Requests': data.metrics.http_req_failed ? `${(data.metrics.http_req_failed.values.rate * 100).toFixed(2)}%` : 'N/A',
        'Avg Response Time': getMetricVal(data.metrics.http_req_duration, 'avg'),
        'p90 Response Time': getMetricVal(data.metrics.http_req_duration, 'p(90)'),
        'p95 Response Time': getMetricVal(data.metrics.http_req_duration, 'p(95)'),
        'p99 Response Time': getMetricVal(data.metrics.http_req_duration, 'p(99)'),
        'Exam Start p95': getMetricVal(data.metrics.exam_start_duration, 'p(95)'),
        'Answer Save p95': getMetricVal(data.metrics.answer_save_duration, 'p(95)'),
        'Exam Submit p95': getMetricVal(data.metrics.exam_submit_duration, 'p(95)'),
    };

    console.log('\n' + '='.repeat(50));
    for (const [key, val] of Object.entries(summary)) {
        console.log(`${key}: ${val}`);
    }
    console.log('='.repeat(50) + '\n');

    return {
        stdout: JSON.stringify(data, null, 2),
    };
}
