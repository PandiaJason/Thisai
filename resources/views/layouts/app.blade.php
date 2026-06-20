<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', 'THISAI IAS Academy')</title>
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
    
    <!-- AlpineJS & ChartJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 text-slate-800 flex flex-col antialiased">

    <!-- Header Navigation -->
    <header class="sticky top-0 z-40 glass-card border-b border-slate-200/60 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand logo -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="THISAI Logo" class="h-14 w-14 object-contain">
                        <div class="flex flex-col">
                            <span class="text-lg font-black tracking-tight text-gradient leading-none">THISAI</span>
                            <span class="text-[9px] uppercase font-bold tracking-widest text-blue-500 mt-0.5">IAS ACADEMY</span>
                        </div>
                    </a>
                    <!-- Desktop nav items -->
                    <nav class="hidden md:flex items-center gap-6">
                        <a href="{{ route('dashboard') }}" class="text-sm font-semibold {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-slate-600 hover:text-blue-600' }} transition-colors">Dashboard</a>
                        <a href="{{ route('courses.index') }}" class="text-sm font-semibold {{ request()->routeIs('courses.*') ? 'text-blue-600' : 'text-slate-600 hover:text-blue-600' }} transition-colors">Courses</a>
                        <a href="{{ route('exams.index') }}" class="text-sm font-semibold {{ request()->routeIs('exams.*') ? 'text-blue-600' : 'text-slate-600 hover:text-blue-600' }} transition-colors">Test Series</a>
                        <a href="{{ route('current-affairs.index') }}" class="text-sm font-semibold {{ request()->routeIs('current-affairs.*') ? 'text-blue-600' : 'text-slate-600 hover:text-blue-600' }} transition-colors">Current Affairs</a>
                        <a href="{{ route('leaderboard.index') }}" class="text-sm font-semibold {{ request()->routeIs('leaderboard.*') ? 'text-blue-600' : 'text-slate-600 hover:text-blue-600' }} transition-colors">Rankings</a>
                    </nav>
                </div>

                <!-- Right header tools -->
                <div class="flex items-center gap-4">
                    <!-- Global Search -->
                    <form action="{{ route('search') }}" method="GET" class="hidden sm:block relative">
                        <input type="text" name="q" placeholder="Search..." class="w-48 bg-slate-100 border border-slate-200 rounded-full px-4 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all">
                    </form>

                    <!-- Notifications bell -->
                    <a href="{{ route('notifications.index') }}" class="relative p-1.5 rounded-full text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                        @if($unreadCount > 0)
                            <span class="absolute top-0 right-0 w-4 h-4 bg-blue-600 text-[10px] font-bold text-white rounded-full flex items-center justify-center">{{ $unreadCount }}</span>
                        @endif
                    </a>

                    <!-- Theme Toggle -->
                    <button id="theme-toggle" type="button" class="p-1.5 rounded-full text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors focus:outline-none" aria-label="Toggle dark mode">
                        <!-- Sun Icon (shown in dark mode) -->
                        <svg id="theme-toggle-light-icon" class="hidden w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                        </svg>
                        <!-- Moon Icon (shown in light mode) -->
                        <svg id="theme-toggle-dark-icon" class="hidden w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- User avatar dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
                            <div class="w-9 h-9 rounded-full bg-blue-100 border-2 border-blue-300 flex items-center justify-center overflow-hidden">
                                @if(auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="font-bold text-sm text-blue-600">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                @endif
                            </div>
                        </button>
                        <!-- Dropdown items -->
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 rounded-xl bg-white border border-slate-200 shadow-xl py-1 z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">Profile Settings</a>
                            @if(auth()->user()->role === \App\Enums\UserRole::SUPER_ADMIN)
                                <a href="/admin" class="block px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">Admin Panel</a>
                            @endif
                            @if(auth()->user()->role === \App\Enums\UserRole::FACULTY)
                                <a href="/faculty" class="block px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">Faculty Panel</a>
                            @endif
                            <hr class="border-slate-100 my-1">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors">Sign Out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-xs"></div>
    <x-toast />

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-400">
        <p>&copy; {{ date('Y') }} THISAI IAS Academy. All rights reserved.</p>
    </footer>

    <!-- Theme Toggle Interactivity Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggleBtn = document.getElementById('theme-toggle');
            if (themeToggleBtn) {
                const lightIcon = document.getElementById('theme-toggle-light-icon');
                const darkIcon = document.getElementById('theme-toggle-dark-icon');
                
                function updateToggleIcons() {
                    if (document.documentElement.classList.contains('dark')) {
                        lightIcon.classList.remove('hidden');
                        darkIcon.classList.add('hidden');
                    } else {
                        lightIcon.classList.add('hidden');
                        darkIcon.classList.remove('hidden');
                    }
                }
                
                updateToggleIcons();
                
                themeToggleBtn.addEventListener('click', () => {
                    if (document.documentElement.classList.contains('dark')) {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    } else {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    }
                    updateToggleIcons();
                    window.dispatchEvent(new Event('theme-changed'));
                });
            }
        });
    </script>
</body>
</html>
