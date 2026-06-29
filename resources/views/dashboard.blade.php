@extends('layouts.app')

@section('title', 'Student Dashboard - THISAI')

@section('content')
<div class="space-y-8">

    <!-- Welcome Banner with Streak & Live Alert -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 p-8 border border-blue-500/20 shadow-2xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="absolute w-64 h-64 bg-white/10 rounded-full blur-3xl -top-24 -left-24"></div>
        <div class="absolute w-64 h-64 bg-purple-500/10 rounded-full blur-3xl -bottom-24 -right-24"></div>

        <div class="relative z-10 space-y-2">
            <span class="text-xs uppercase font-bold tracking-widest text-white bg-white/20 px-3 py-1 rounded-full border border-white/30">Candidate Portal</span>
            <h1 class="text-3xl font-extrabold text-white">Hello, {{ auth()->user()->name }}</h1>
            <p class="text-blue-100 text-sm max-w-xl">"Success is not final, failure is not fatal: it is the courage to continue that counts." Access your daily IAS syllabus preparation modules below.</p>
        </div>

        <div class="flex items-center gap-6 shrink-0 relative z-10">
            <!-- Streak Fire Badge -->
            <div class="flex items-center gap-3 bg-white/10 border border-white/20 px-4 py-3 rounded-xl shadow-lg">
                <svg class="w-8 h-8 text-amber-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <div>
                    <span class="text-[10px] uppercase font-bold text-blue-100 tracking-wider block">Prep Streak</span>
                    <span class="text-lg font-black text-white block leading-none mt-1">{{ $profile->current_streak ?? 0 }} Days</span>
                </div>
            </div>

            <!-- Rank Trend Badge -->
            @if($currentRank)
                <div class="flex items-center gap-3 bg-white/10 border border-white/20 px-4 py-3 rounded-xl shadow-lg">
                    <div class="w-8 h-8 rounded-full bg-white/25 flex items-center justify-center font-black text-white text-xs border border-white/30">
                        #{{ $currentRank }}
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-blue-100 tracking-wider block">Weekly Rank</span>
                        <span class="text-sm font-black text-white flex items-center gap-1 mt-1 leading-none">
                            Rank {{ $currentRank }}
                            @if($rankChange !== null && $rankChange !== 0)
                                <span class="{{ $rankChange > 0 ? 'text-emerald-400' : 'text-red-400' }} text-[10px] font-extrabold flex items-center">
                                    {{ $rankChange > 0 ? '▲' : '▼' }} {{ abs($rankChange) }}
                                </span>
                            @endif
                        </span>
                    </div>
                </div>
            @endif
        </div>

        @if($liveTelecast)
            <div class="relative z-10 flex items-center gap-4 bg-white/10 border border-white/20 p-4 rounded-xl max-w-sm">
                <span class="w-3.5 h-3.5 bg-red-500 rounded-full pulse-red-dot shrink-0"></span>
                <div class="text-xs space-y-1">
                    <span class="font-bold text-white block uppercase tracking-wider">Morning Telecast is Live!</span>
                    <p class="text-blue-100">Join the discussion now. This video will expire tonight at {{ $liveTelecast->auto_delete_at->format('h:i A') }}.</p>
                    <a href="#live-session" class="inline-block mt-1 font-bold text-white hover:text-blue-200 transition-colors">Tune in now &rarr;</a>
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card-blue glass-card p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-blue-100 tracking-wider">Enrolled Courses</span>
                <span class="text-3xl font-black block text-white mt-0.5">{{ $analytics['enrolled_courses'] }}</span>
            </div>
        </div>

        <div class="stat-card-purple glass-card p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-purple-100 tracking-wider">Videos Watched</span>
                <span class="text-3xl font-black block text-white mt-0.5">{{ $analytics['videos_watched'] }}</span>
            </div>
        </div>

        <div class="stat-card-green glass-card p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-emerald-100 tracking-wider">Tests Taken</span>
                <span class="text-3xl font-black block text-white mt-0.5">{{ $analytics['exams_attempted'] }}</span>
            </div>
        </div>

        <div class="stat-card-amber glass-card p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-amber-100 tracking-wider">Average Score</span>
                <span class="text-3xl font-black block text-white mt-0.5">{{ $analytics['avg_score'] }}%</span>
            </div>
        </div>
    </div>


    <!-- Custom Whiteboard CSS styles -->
    <style>
        .canvas-grid {
            background-color: #ffffff !important;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px) !important;
            background-size: 20px 20px !important;
        }
        .canvas-ruled {
            background-color: #ffffff !important;
            background-image: linear-gradient(#e2e8f0 1px, transparent 1px) !important;
            background-size: 100% 28px !important;
        }
        .canvas-blank {
            background-color: #ffffff !important;
        }
        .active-color-btn {
            box-shadow: 0 0 0 2px #3b82f6 !important;
            border-color: #ffffff !important;
        }
    </style>

    <!-- Live Telecast Section -->
    @if($liveTelecast)
        <div id="live-session" class="glass-card p-6 rounded-2xl border border-red-500/20 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-3">
                    <span class="px-2 py-0.5 rounded bg-red-600 text-[10px] font-bold text-white uppercase tracking-wider pulse-red-dot flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 bg-white rounded-full"></span> Live
                    </span>
                    <h2 class="text-lg font-bold text-slate-800">{{ $liveTelecast->title }}</h2>
                </div>
                <div class="flex items-center gap-3">
                    <button id="toggle-whiteboard-btn" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold transition-all shadow-sm">
                        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        <span>Open Whiteboard</span>
                    </button>
                    <span class="text-xs font-semibold text-slate-500">Expiring tonight at {{ $liveTelecast->auto_delete_at->format('h:i A') }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6" id="live-session-container">
                <!-- Video Player Column -->
                <div class="lg:col-span-5 transition-all duration-300" id="video-column">
                    <div class="aspect-video w-full rounded-xl overflow-hidden bg-black border border-slate-100 shadow-inner">
                        <iframe src="{{ $liveTelecast->stream_url }}" class="w-full h-full" allowfullscreen allow="autoplay; encrypted-media"></iframe>
                    </div>
                </div>

                <!-- Whiteboard Column (Hidden by default, takes col-span-2 when open) -->
                <div class="hidden lg:col-span-2 flex-col bg-slate-50 rounded-xl border border-slate-200/80 p-4 space-y-3 transition-all duration-300" id="whiteboard-column">
                    <!-- Whiteboard Header & Controls -->
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
                        <!-- Colors -->
                        <div class="flex items-center gap-1.5" id="whiteboard-colors">
                            <button data-color="#0f172a" class="w-6 h-6 rounded-full bg-slate-900 border-2 border-transparent hover:border-white hover:ring-2 hover:ring-slate-300 active-color-btn" title="Black"></button>
                            <button data-color="#ef4444" class="w-6 h-6 rounded-full bg-red-500 border-2 border-transparent hover:border-white hover:ring-2 hover:ring-slate-300" title="Red"></button>
                            <button data-color="#3b82f6" class="w-6 h-6 rounded-full bg-blue-500 border-2 border-transparent hover:border-white hover:ring-2 hover:ring-slate-300" title="Blue"></button>
                            <button data-color="#10b981" class="w-6 h-6 rounded-full bg-emerald-500 border-2 border-transparent hover:border-white hover:ring-2 hover:ring-slate-300" title="Green"></button>
                            <button id="whiteboard-eraser-btn" class="w-6 h-6 rounded-full bg-slate-200 border-2 border-transparent hover:border-white hover:ring-2 hover:ring-slate-300 flex items-center justify-center" title="Eraser">
                                <svg class="w-3.5 h-3.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>

                        <!-- Size slider -->
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] uppercase font-bold text-slate-400">Size</span>
                            <input type="range" id="whiteboard-brush-size" min="1" max="20" value="4" class="w-16 h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer">
                            <span id="brush-size-display" class="text-xs font-bold text-slate-500 w-4 text-center">4</span>
                        </div>

                        <!-- Background Patterns -->
                        <div class="flex items-center gap-1 bg-slate-200 p-0.5 rounded-lg">
                            <button data-pattern="blank" class="px-2 py-1 rounded text-[10px] font-bold bg-white text-slate-800 shadow-sm pattern-btn">Blank</button>
                            <button data-pattern="grid" class="px-2 py-1 rounded text-[10px] font-bold text-slate-600 hover:text-slate-800 pattern-btn">Grid</button>
                            <button data-pattern="ruled" class="px-2 py-1 rounded text-[10px] font-bold text-slate-600 hover:text-slate-800 pattern-btn">Ruled</button>
                        </div>
                    </div>

                    <!-- Canvas Container -->
                    <div class="relative flex-1 bg-white border border-slate-200 rounded-lg overflow-hidden shadow-inner min-h-[350px]">
                        <canvas id="whiteboard-canvas" class="w-full h-full bg-white canvas-blank block cursor-crosshair" style="touch-action: none; user-select: none; -webkit-user-select: none;"></canvas>
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                        <div class="flex items-center gap-2">
                            <button id="whiteboard-undo-btn" class="inline-flex items-center justify-center p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all" title="Undo">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                            </button>
                            <button id="whiteboard-clear-btn" class="inline-flex items-center justify-center p-2 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-700 hover:text-red-600 text-xs font-bold transition-all" title="Clear Board">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                        <button id="whiteboard-download-btn" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Save Notes</span>
                        </button>
                    </div>
                </div>
            </div>

            @if($liveTelecast->description)
                <p class="text-slate-600 text-sm leading-relaxed">{{ $liveTelecast->description }}</p>
            @endif
        </div>
    @endif

    <!-- Whiteboard Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.getElementById('toggle-whiteboard-btn');
            const videoColumn = document.getElementById('video-column');
            const whiteboardColumn = document.getElementById('whiteboard-column');
            const canvas = document.getElementById('whiteboard-canvas');
            
            if (!toggleBtn || !canvas) return;
            
            const ctx = canvas.getContext('2d');
            let isDrawing = false;
            let lastX = 0;
            let lastY = 0;
            let currentColor = '#0f172a';
            let currentSize = 4;
            let currentMode = 'draw'; // 'draw' or 'eraser'
            let strokes = []; // Array of strokes
            let currentStroke = null;
            
            // Handle toggle whiteboard
            toggleBtn.addEventListener('click', function() {
                const isOpen = !whiteboardColumn.classList.contains('hidden');
                if (isOpen) {
                    // Close
                    whiteboardColumn.classList.add('hidden');
                    whiteboardColumn.classList.remove('flex');
                    videoColumn.classList.add('lg:col-span-5');
                    videoColumn.classList.remove('lg:col-span-3');
                    toggleBtn.querySelector('span').innerText = 'Open Whiteboard';
                } else {
                    // Open
                    whiteboardColumn.classList.remove('hidden');
                    whiteboardColumn.classList.add('flex');
                    videoColumn.classList.remove('lg:col-span-5');
                    videoColumn.classList.add('lg:col-span-3');
                    toggleBtn.querySelector('span').innerText = 'Close Whiteboard';
                    
                    // Initialize canvas dimensions
                    setTimeout(resizeCanvas, 100);
                }
            });
            
            // Resize Handler
            const resizeCanvas = () => {
                const rect = canvas.parentNode.getBoundingClientRect();
                canvas.width = rect.width;
                canvas.height = rect.height;
                redraw();
            };
            
            window.addEventListener('resize', () => {
                if (!whiteboardColumn.classList.contains('hidden')) {
                    resizeCanvas();
                }
            });
            
            // Drawing coordinates helper
            const getCanvasCoords = (e) => {
                const rect = canvas.getBoundingClientRect();
                return {
                    x: e.clientX - rect.left,
                    y: e.clientY - rect.top
                };
            };
            
            // Touch & Stylus / E-Pen drawing handler using unified Pointer Events
            canvas.addEventListener('pointerdown', function(e) {
                isDrawing = true;
                const coords = getCanvasCoords(e);
                lastX = coords.x;
                lastY = coords.y;
                
                currentStroke = {
                    color: currentColor,
                    size: currentSize,
                    mode: currentMode,
                    pointerType: e.pointerType,
                    points: [{ x: coords.x, y: coords.y, pressure: e.pressure || 0.5 }]
                };
                strokes.push(currentStroke);
                
                // Draw a dot immediately on press
                ctx.beginPath();
                ctx.arc(lastX, lastY, (currentSize / 2) * (e.pressure ? e.pressure * 1.5 : 1), 0, Math.PI * 2);
                ctx.fillStyle = currentMode === 'eraser' ? '#ffffff' : currentColor;
                ctx.fill();
                
                e.preventDefault();
            });
            
            canvas.addEventListener('pointermove', function(e) {
                if (!isDrawing) return;
                
                const coords = getCanvasCoords(e);
                
                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(coords.x, coords.y);
                
                // Read pen pressure to scale line width dynamically (calligraphy/natural strokes!)
                const pressureScale = e.pressure && e.pointerType === 'pen' ? e.pressure * 1.8 : 1.0;
                ctx.lineWidth = currentSize * pressureScale;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.strokeStyle = currentMode === 'eraser' ? '#ffffff' : currentColor;
                ctx.stroke();
                
                currentStroke.points.push({ x: coords.x, y: coords.y, pressure: e.pressure || 0.5 });
                
                lastX = coords.x;
                lastY = coords.y;
                
                e.preventDefault();
            });
            
            const stopDrawing = (e) => {
                isDrawing = false;
                currentStroke = null;
            };
            
            canvas.addEventListener('pointerup', stopDrawing);
            canvas.addEventListener('pointercancel', stopDrawing);
            canvas.addEventListener('pointerleave', stopDrawing);
            
            // Redraw history
            const redraw = () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                strokes.forEach(stroke => {
                    if (stroke.points.length === 0) return;
                    
                    if (stroke.points.length === 1) {
                        ctx.beginPath();
                        const p = stroke.points[0];
                        ctx.arc(p.x, p.y, (stroke.size / 2) * (p.pressure ? p.pressure * 1.5 : 1), 0, Math.PI * 2);
                        ctx.fillStyle = stroke.mode === 'eraser' ? '#ffffff' : stroke.color;
                        ctx.fill();
                        return;
                    }
                    
                    ctx.beginPath();
                    ctx.moveTo(stroke.points[0].x, stroke.points[0].y);
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';
                    ctx.strokeStyle = stroke.mode === 'eraser' ? '#ffffff' : stroke.color;
                    
                    for (let i = 1; i < stroke.points.length; i++) {
                        const p = stroke.points[i];
                        const pressureScale = p.pressure && stroke.pointerType === 'pen' ? p.pressure * 1.8 : 1.0;
                        ctx.lineWidth = stroke.size * pressureScale;
                        ctx.lineTo(p.x, p.y);
                    }
                    ctx.stroke();
                });
            };
            
            // Brush Color Selection
            const colorBtns = document.querySelectorAll('#whiteboard-colors button[data-color]');
            colorBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    currentColor = btn.getAttribute('data-color');
                    currentMode = 'draw';
                    
                    colorBtns.forEach(b => b.classList.remove('active-color-btn'));
                    btn.classList.add('active-color-btn');
                    document.getElementById('whiteboard-eraser-btn').classList.remove('active-color-btn');
                });
            });
            
            // Eraser Selection
            const eraserBtn = document.getElementById('whiteboard-eraser-btn');
            eraserBtn.addEventListener('click', function() {
                currentMode = 'eraser';
                colorBtns.forEach(b => b.classList.remove('active-color-btn'));
                eraserBtn.classList.add('active-color-btn');
            });
            
            // Brush Size Slider
            const sizeSlider = document.getElementById('whiteboard-brush-size');
            const sizeDisplay = document.getElementById('brush-size-display');
            sizeSlider.addEventListener('input', function() {
                currentSize = parseInt(sizeSlider.value);
                sizeDisplay.innerText = currentSize;
            });
            
            // Background Pattern Toggle
            const patternBtns = document.querySelectorAll('.pattern-btn');
            patternBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const pattern = btn.getAttribute('data-pattern');
                    
                    canvas.className = 'w-full h-full block cursor-crosshair';
                    canvas.classList.add('canvas-' + pattern);
                    
                    patternBtns.forEach(b => {
                        b.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
                        b.classList.add('text-slate-600', 'hover:text-slate-800');
                    });
                    btn.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
                    btn.classList.remove('text-slate-600', 'hover:text-slate-800');
                });
            });
            
            // Undo Action
            document.getElementById('whiteboard-undo-btn').addEventListener('click', function() {
                strokes.pop();
                redraw();
            });
            
            // Clear Board Action
            document.getElementById('whiteboard-clear-btn').addEventListener('click', function() {
                if (confirm('Clear entire whiteboard?')) {
                    strokes = [];
                    redraw();
                }
            });
            
            // Download Notes Action
            document.getElementById('whiteboard-download-btn').addEventListener('click', function() {
                const exportCanvas = document.createElement('canvas');
                exportCanvas.width = canvas.width;
                exportCanvas.height = canvas.height;
                const exportCtx = exportCanvas.getContext('2d');
                
                exportCtx.fillStyle = '#ffffff';
                exportCtx.fillRect(0, 0, canvas.width, canvas.height);
                
                if (canvas.classList.contains('canvas-grid')) {
                    exportCtx.strokeStyle = '#e2e8f0';
                    exportCtx.lineWidth = 1;
                    for (let x = 0; x < canvas.width; x += 20) {
                        for (let y = 0; y < canvas.height; y += 20) {
                            exportCtx.beginPath();
                            exportCtx.arc(x, y, 1, 0, Math.PI * 2);
                            exportCtx.fillStyle = '#cbd5e1';
                            exportCtx.fill();
                        }
                    }
                } else if (canvas.classList.contains('canvas-ruled')) {
                    exportCtx.strokeStyle = '#e2e8f0';
                    exportCtx.lineWidth = 1;
                    for (let y = 28; y < canvas.height; y += 28) {
                        exportCtx.beginPath();
                        exportCtx.moveTo(0, y);
                        exportCtx.lineTo(canvas.width, y);
                        exportCtx.stroke();
                    }
                }
                
                strokes.forEach(stroke => {
                    if (stroke.points.length === 0) return;
                    
                    if (stroke.points.length === 1) {
                        exportCtx.beginPath();
                        const p = stroke.points[0];
                        exportCtx.arc(p.x, p.y, (stroke.size / 2) * (p.pressure ? p.pressure * 1.5 : 1), 0, Math.PI * 2);
                        exportCtx.fillStyle = stroke.mode === 'eraser' ? '#ffffff' : stroke.color;
                        exportCtx.fill();
                        return;
                    }
                    
                    exportCtx.beginPath();
                    exportCtx.moveTo(stroke.points[0].x, stroke.points[0].y);
                    exportCtx.lineCap = 'round';
                    exportCtx.lineJoin = 'round';
                    exportCtx.strokeStyle = stroke.mode === 'eraser' ? '#ffffff' : stroke.color;
                    
                    for (let i = 1; i < stroke.points.length; i++) {
                        const p = stroke.points[i];
                        const pressureScale = p.pressure && stroke.pointerType === 'pen' ? p.pressure * 1.8 : 1.0;
                        exportCtx.lineWidth = stroke.size * pressureScale;
                        exportCtx.lineTo(p.x, p.y);
                    }
                    exportCtx.stroke();
                });
                
                const dataUrl = exportCanvas.toDataURL('image/png');
                const link = document.createElement('a');
                link.download = 'thisai-whiteboard-notes.png';
                link.href = dataUrl;
                link.click();
            });
        });
    </script>

    <!-- Content Split Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Continue Learning & Current Affairs (2/3 width) -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Continue Learning -->
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1 h-5 bg-blue-500 rounded-full"></span> Continue Learning
                </h3>
                @if($recentVideos->isEmpty())
                    <div class="glass-card p-8 rounded-xl text-center space-y-3">
                        <p class="text-slate-400 text-sm">No videos in progress. Start exploring courses.</p>
                        <a href="{{ route('courses.index') }}" class="inline-flex bg-blue-600 hover:bg-blue-500 px-4 py-1.5 text-xs font-bold text-white rounded-full transition-colors">Browse Catalog</a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($recentVideos as $video)
                            @php $progress = $video->getUserProgress(auth()->user()); @endphp
                            <div class="glass-card p-4 rounded-xl flex flex-col justify-between gap-4 border-l-4 border-blue-500">
                                <div>
                                    <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">{{ $video->course->title }}</span>
                                    <h4 class="font-bold text-slate-800 mt-1 text-sm line-clamp-1">{{ $video->title }}</h4>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs font-semibold text-slate-400">
                                        <span>Progress</span>
                                        <span>{{ $progress ? $progress->progress_percent : 0 }}%</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500" style="width: {{ $progress ? $progress->progress_percent : 0 }}%"></div>
                                    </div>
                                    <a href="{{ route('videos.watch', $video->id) }}" class="inline-flex w-full items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 rounded-lg text-xs transition-colors">Resume Lesson</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Daily Current Affairs Carousel -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-1 h-5 bg-purple-500 rounded-full"></span> Today's Current Affairs
                    </h3>
                    <a href="{{ route('current-affairs.index') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-850">View Archive &rarr;</a>
                </div>

                @if($currentAffairs->isEmpty())
                    <div class="glass-card p-8 rounded-xl text-center">
                        <p class="text-slate-400 text-sm">No articles published for today yet. Check back later.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($currentAffairs as $article)
                            <div class="glass-card p-5 rounded-xl space-y-3 relative flex flex-col justify-between">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold text-purple-600 uppercase tracking-widest bg-purple-50 px-2.5 py-0.5 rounded border border-purple-100">{{ $article->type->label() }}</span>
                                        @if($article->subject)
                                            <span class="text-[10px] font-semibold text-slate-500">{{ $article->subject->name }}</span>
                                        @endif
                                    </div>
                                    <h4 class="font-bold text-slate-800 text-sm line-clamp-2"><a href="{{ route('current-affairs.show', $article->slug) }}" class="hover:text-purple-500 transition-colors">{{ $article->title }}</a></h4>
                                </div>
                                <div class="flex items-center justify-between border-t border-slate-200 pt-3 mt-2 text-xs">
                                    <span class="text-slate-500">{{ $article->publish_date->format('M d, Y') }}</span>
                                    <a href="{{ route('current-affairs.show', $article->slug) }}" class="font-bold text-blue-600 hover:text-blue-800 transition-colors">Read Article &rarr;</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Weekly Progress Graph -->
            <div class="glass-card p-6 rounded-2xl">
                <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-emerald-500 rounded-full"></span> Recent Test Score Analytics
                </h3>
                <div class="h-64">
                    <canvas id="weeklyProgressChart"></canvas>
                </div>
            </div>

            <!-- Achievements & UPSC Badges -->
            <div class="glass-card p-6 rounded-2xl space-y-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="w-1 h-5 bg-amber-500 rounded-full"></span> Unlocked UPSC Achievements
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach($badges as $badge)
                        @php $isUnlocked = in_array($badge->id, $unlockedBadgeIds); @endphp
                        <div class="flex flex-col items-center text-center p-3 rounded-xl border {{ $isUnlocked ? 'border-amber-100 bg-amber-500/5' : 'border-slate-100 bg-slate-500/5 opacity-40' }} transition-all duration-300">
                            <div class="relative mb-2">
                                {!! $badge->icon_svg !!}
                                @if($isUnlocked)
                                    <span class="absolute -bottom-1 -right-1 bg-amber-500 text-white rounded-full p-0.5 border border-white flex items-center justify-center w-4.5 h-4.5 shadow-sm animate-bounce" title="Unlocked">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                @else
                                    <span class="absolute -bottom-1 -right-1 bg-slate-400 text-white rounded-full p-0.5 border border-white flex items-center justify-center w-4.5 h-4.5" title="Locked">
                                        <svg class="w-2.5 h-2.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs font-bold text-slate-800 leading-tight">{{ $badge->name }}</span>
                            <span class="text-[9px] text-slate-500 leading-tight mt-1">{{ $badge->description }}</span>
                            @if($isUnlocked)
                                <span class="text-[9px] font-extrabold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded-full mt-1.5 leading-none">+{{ $badge->points }} XP</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- Right: Rankings & Upcoming Tests Sidebar (1/3 width) -->
        <div class="space-y-8">
            
            <!-- Upcoming / Available Exams -->
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1 h-5 bg-amber-500 rounded-full"></span> Available Test Series
                </h3>
                <div class="space-y-3">
                    @foreach($upcomingTests as $exam)
                        @php $attempt = $exam->getUserAttempt(auth()->user()); @endphp
                        <div class="glass-card p-4 rounded-xl space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-100">{{ $exam->type->label() }}</span>
                                <span class="text-xs font-semibold text-slate-500">{{ $exam->duration_minutes }} Mins</span>
                            </div>
                            <h4 class="font-bold text-slate-800 text-sm line-clamp-1">{{ $exam->title }}</h4>
                            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200">
                                <span class="text-slate-500">Marks: {{ $exam->total_marks }}</span>
                                @if($attempt)
                                    <span class="text-emerald-700 font-bold text-[10px] uppercase bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">Attempted</span>
                                @else
                                    <form action="{{ route('exams.start', $exam->slug) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="font-bold text-blue-600 hover:text-blue-800 transition-colors">Start Test &rarr;</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Leaderboard Leader previews -->
            <div class="glass-card p-6 rounded-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-1 h-5 bg-blue-500 rounded-full"></span> Weekly Ranks
                    </h3>
                    <a href="{{ route('leaderboard.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">View All &rarr;</a>
                </div>
                <div class="space-y-3">
                    @forelse($topStudents as $student)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs {{ $student->rank == 1 ? 'bg-yellow-100 text-yellow-800 border border-yellow-300' : ($student->rank == 2 ? 'bg-slate-100 text-slate-800 border border-slate-300' : ($student->rank == 3 ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-slate-50 text-slate-600 border border-slate-200')) }}">
                                    {{ $student->rank }}
                                </span>
                                <span class="text-sm text-slate-700 font-medium">{{ $student->user->name }}</span>
                            </div>
                            <span class="text-sm font-bold text-slate-800">{{ round($student->total_score) }} pts</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">No rankings generated yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Chart Script -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('weeklyProgressChart').getContext('2d');
        
        // Prepare chart labels and data
        const attempts = {!! json_encode($weeklyAttempts->map(fn($a) => [
            'date' => $a->submitted_at->format('M d'),
            'score' => $a->score
        ])) !!};
        
        const labels = attempts.map(a => a.date);
        const data = attempts.map(a => a.score);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['No Test Data'],
                datasets: [{
                    label: 'Marks Obtained',
                    data: data.length ? data : [0],
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: 'rgb(16, 185, 129)',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: { color: '#64748b' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b' }
                    }
                }
            }
        });
    });
</script>
@endsection
