<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', 'Examination') - THISAI</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Theme Initializer (Sync with Filament) -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex flex-col antialiased select-none">

    <!-- Distraction-free Exam Header -->
    <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-850 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-xl font-bold tracking-tight text-gradient">THISAI</a>
                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded border border-blue-100">Exam Engine</span>
            </div>
            
            <div class="text-center font-bold text-sm text-slate-800">
                @yield('exam-title')
            </div>

            <div class="flex items-center gap-4 text-slate-800">
                @yield('timer-section')
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 overflow-hidden flex">
        @yield('content')
    </main>

    <!-- Block back button & tab switching script -->
    <script>
        // Warning when trying to close tab or reload
        window.addEventListener('beforeunload', function (e) {
            e.preventDefault();
            e.returnValue = 'Are you sure you want to end the exam? Your progress will be saved but this action cannot be undone.';
        });

        // Tab switching detection
        let tabSwitchCount = 0;
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                tabSwitchCount++;
                if (tabSwitchCount >= 3) {
                    alert('CRITICAL WARNING: Tab switching is strictly prohibited during the exam. Your attempt will be automatically submitted if you switch tabs again.');
                } else {
                    alert(`WARNING: Do not switch tabs. Tab switch detected (${tabSwitchCount}/3).`);
                }
            }
        });
    </script>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-xs"></div>
    <x-toast />
</body>
</html>
