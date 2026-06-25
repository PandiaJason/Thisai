# THISAI IAS Academy Platform

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Filament Version](https://img.shields.io/badge/Filament-4.x-amber.svg)](https://filamentphp.com)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.x-blue.svg)](https://tailwindcss.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

THISAI is a premium, high-performance, dark-mode-first ed-tech platform specifically designed for IAS aspirants. Built using **Laravel 12**, **Filament 4**, and **Docker**, it features a fully-functional course library, secure video streaming via Bunny Stream, distraction-free secure exam engines, and real-time student leaderboards.

---

## 🚀 Key Features

### 👨‍🎓 Student Portal
- **Motivating Dashboard**: Features welcome cards with dynamic quotes, daily streaks, quick stats (courses enrolled, watched videos, tests taken), a weekly score chart (Chart.js), and daily current affairs.
- **Course Player & Catalog**: Premium course layout filterable by subjects. Includes video player page integrated with Bunny Stream, and checkmark trackable accordion curriculum.
- **Daily Current Affairs**: Filterable by source (Daily, Editorial, PIB, Schemes, Facts) with bookmarks and date navigation.
- **Real-Time Leaderboards**: Showcases daily, weekly, monthly, and overall top performers on an animated 3-tier podium.
- **Student Analytics**: A detailed dashboard featuring score trends, subject performance heatmaps, weak areas, and a timeline tracking improvement metrics over time.
- **Discussion Forums**: Full-featured community doubt-solving boards allowing students to create threads, post replies, upvote/downvote discussions, and mark questions as resolved.
- **Performance Certificates**: Earn verified completion certificates upon completing courses and exams, backed by a public verification system (`/certificates/verify/{id}`) and downloadable PDFs.

### 📝 Interactive Secure Exam Engine
- **Secure Exam Mode Overlay**: Enforces fullscreen mode via HTML5 API. Exiting fullscreen, switching tabs, or resizing warning counts are logged.
- **Staggered AJAX Save**: Auto-saves student selections on-the-fly via API to prevent answer loss.
- **Palette Navigation**: Visual color-coded grid representing visited, skipped, answered, and current states.
- **USP: Time & Subject Analytics**: Integrates a detailed scorecard featuring a **Time vs. Correctness Matrix** (classifying questions into Quick/Medium/Slow vs. Correct/Wrong to track near-misses, overthinking, and careless errors) alongside an interactive Subject Radar Chart.

### 🏛️ Filament 4 Panels (Admin & Faculty)
- **Admin Panel (`/admin`)**: Complete student, course, batch, exam, subject, current affairs, live stream, and system audit log management. Inherits all Faculty resource tools for Super Admins.
- **Faculty Panel (`/faculty`)**: Restricted views allowing instructors to manage courses, add chapters/videos, configure exams, write current affairs, and schedule live streams.
- **Faculty Exam Analytics**: Custom panel featuring CSV results exports, question difficulty indices, and lists of the top 10 most missed questions.
- **Standalone Question Bank & Bulk Import**: Manage questions globally independent of exams. Features a **Bulk Import** wizard that parses raw text/CSV question formats automatically.

### 🎨 Live Class Interactive Whiteboard
- **Split-Screen Interactive Layout**: Seamlessly opens a side-by-side whiteboard canvas next to the live video classes player.
- **Active E-Pen & Stylus Support**: Developed using the HTML5 Pointer Events API for full compatibility with Apple Pencil, Samsung S-Pen, etc. Supports dynamic pressure sensitivity (calligraphy strokes) and palm rejection.
- **Grid & Notebook Layouts**: Instantly toggles canvas backgrounds between Plain, Grid, and Ruled templates. Includes undo, canvas clear, and save-as-PNG notes actions.

### 🛡️ Security & Queue Infrastructure
- **Brute-Force Rate Limiting**: Enforces strict throttling policies on auth routes (login, registration, password resets).
- **Asynchronous Processing**: Background workers powered by Redis process high-load tasks asynchronously, including leaderboard recalculations and result email notifications.
- **Scheduled Auto-Submissions**: An automated schedule command checks hourly for active attempts exceeding the max duration, force-submitting them server-side.

---

## 🛠️ Tech Stack & Architecture

- **Backend**: Laravel 12 (PHP 8.2+)
- **Panels**: Filament v4 (Administration & Instructor panel)
- **Frontend**: Blade Templates, TailwindCSS v4, AlpineJS, Chart.js
- **Database & Cache**: PostgreSQL (Primary database), Redis (Queues, session caching)
- **Streaming**: Bunny.net Stream Integration (Signed token URLs for hotlink protection)
- **Environment**: Docker (PHP-FPM, Nginx, Postgres, Redis, Mailpit)

---

## 📦 Docker Setup & Installation

Follow these steps to run the project locally via Docker:

### 1. Clone the repository and configure Environment
```bash
cp .env.example .env
```
Ensure your database parameters in `.env` match the Docker config:
```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=thisai
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

### 2. Start Docker Containers
Build and run the containers in the background:
```bash
docker compose up -d --build
```

### 3. Install Dependencies
Run Composer and NPM installs inside the container:
```bash
docker compose exec app composer install
docker compose exec app npm install
```

### 4. Database Setup & Seeding
Run migrations and load pre-configured IAS subjects (Polity, History, Geography, Economics, Environment, Science & Tech, Current Affairs) along with an admin account:
```bash
docker compose exec app php artisan migrate --seed
```
* **Super Admin Credentials**: `admin@thisai.com` | `password`

### 5. Build Frontend Assets
Compile TailwindCSS v4 and AlpineJS scripts:
```bash
docker compose exec app npm run build
```
Access the application at `http://localhost:8000`.

---

## 🧪 Testing
The codebase is fully covered by automated integration tests checking authentication flow, student answer saving, score calculation, and exam completion.

Run the test suite using:
```bash
docker compose exec app php artisan test
```

---

## 🛡️ Security & Anti-Cheating Protocols

THISAI includes robust security constraints to protect exam integrity, user data, and premium assets:

- **Anti-Piracy Video Protection (Bunny.net HLS Token Security)**: Prevents video leaks and link sharing by generating temporary SHA256-signed URLs (valid for 1 hour only) hashed with a secure private key.
- **Proctoring & Fullscreen Enforcement**: Restricts exam screens to fullscreen mode. Detects browser visibility changes, tab-switches, and window resizing, automatically locking the student or submitting the exam on threshold violations.
- **SQL Injection Defenses**: Built entirely using Laravel Eloquent ORM which utilizes PDO parameter bindings to render user inputs completely inert to query-injection attacks.
- **Backend Time-Tampering Validation**: Evaluates test timers server-side by validating attempt start timestamps against real clock servers, completely neutralizing client-side javascript clock alterations.
- **Session-Locked Submissions**: Protects exam states via a unique, cryptographically random 40-character token. All answer save APIs enforce ownership matching to reject parameter-tampering hacks.

---

## 📊 Scalability & Performance Details

### Current Performance (Single Instance)
- **Concurrent Users**: Comfortably handles **200 to 500 concurrent users** taking a test simultaneously.
- **Staggered Syncing**: Answer saving during exams is highly scalable because requests are spread out (1 request per student every 30-60 seconds on average).

### Key Production Bottlenecks & Optimization
1. **Bulk inserts on Exam Start**: When a student clicks "Start", the system initializes empty answer templates for all questions. To scale beyond 1,000 concurrent starts, the sequential `create()` loop should be refactored into a single database `insert()` query.
2. **On-Submit Rank Recalculation**: Submitting a test triggers an $O(N)$ ranking update loop for all exam submissions. For large groups (e.g., 5,000+ simultaneous submissions), this rank computation should be dispatched to a background queued job or calculated dynamically during query time using PostgreSQL window functions:
   ```sql
   RANK() OVER (ORDER BY score DESC)
   ```

---

## 📄 License
This project is open-sourced software licensed under the [MIT License](LICENSE).
