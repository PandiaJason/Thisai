<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', 'Welcome') - THISAI IAS Academy</title>
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
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex flex-col sm:flex-row antialiased overflow-hidden">

    <!-- Left Side: Branding and details -->
    <div class="hidden md:flex md:w-1/2 p-12 flex-col justify-between relative overflow-hidden border-r border-slate-200" style="background: linear-gradient(135deg, #f0f7ff, #e0f2fe, #e2e8f0);">
        <!-- Elite Mesh Glow Background (Subtle & Elegant light blobs) -->
        <div class="absolute w-[500px] h-[500px] bg-sky-300/30 rounded-full blur-[100px] -top-32 -left-32 pointer-events-none"></div>
        <div class="absolute w-[600px] h-[600px] bg-indigo-300/25 rounded-full blur-[130px] top-1/4 -left-48 pointer-events-none"></div>
        <div class="absolute w-[450px] h-[450px] bg-blue-300/30 rounded-full blur-[100px] -bottom-24 -right-16 pointer-events-none"></div>
        
        <div class="relative z-10">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo.png') }}" alt="THISAI Logo" class="h-28 w-28 object-contain">
                <div class="flex flex-col">
                    <span class="text-3xl font-black tracking-tight text-gradient leading-none">THISAI</span>
                    <span class="text-sm uppercase font-bold tracking-widest text-blue-600 mt-1.5">IAS ACADEMY</span>
                </div>
            </div>
            <p class="text-slate-600 mt-3 text-sm font-semibold max-w-sm">Premium Learning Management & MCQ Examination Platform</p>
        </div>

        <div class="max-w-md my-auto relative z-10">
            <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 mb-4 leading-tight">Elevate Your Civil Services Preparation</h1>
            <p class="text-slate-600 text-sm leading-relaxed mb-6">Access top-tier video lessons, daily current affairs analysis, and full-length exam mock series designed by experienced faculty mentors.</p>
            
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-3 bg-white/70 backdrop-blur-md p-3 rounded-xl shadow-md transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/5" style="border: 1px solid rgba(59, 130, 246, 0.12);">
                    <span class="w-2.5 h-2.5 bg-red-500 rounded-full pulse-red-dot"></span>
                    <div class="text-xs">
                        <span class="font-bold text-slate-800 block">Daily Live Telecast</span>
                        <span class="text-slate-600">Join interactive sessions every morning from 06:00 AM - 07:00 AM.</span>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-white/70 backdrop-blur-md p-3 rounded-xl shadow-md transition-all duration-300 hover:shadow-lg hover:shadow-purple-500/5" style="border: 1px solid rgba(139, 92, 246, 0.12);">
                    <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                    <div class="text-xs">
                        <span class="font-bold text-slate-800 block">Normalized Percentiles</span>
                        <span class="text-slate-600">Understand your standing with real-time peer analysis & rank scoring.</span>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-xs text-slate-500 relative z-10">&copy; {{ date('Y') }} THISAI IAS Academy. Empowering leaders of tomorrow.</p>
    </div>

    <!-- Right Side: Guest View Form -->
    <div class="w-full md:w-1/2 flex items-center justify-center p-6 sm:p-12 overflow-y-auto h-full bg-slate-50 dark:bg-slate-950">
        @yield('content')
    </div>

</body>
</html>
