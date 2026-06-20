<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Admin\Pages\Auth\Login::class)
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => new HtmlString('
                    <style>
                        /* Centered simple layouts logo styling (Login page) */
                        .fi-simple-layout .fi-logo,
                        .fi-simple-layout img,
                        .fi-simple-header img {
                            height: 7.5rem !important;
                            width: auto !important;
                            max-width: 100% !important;
                            object-fit: contain !important;
                        }

                        /* LIGHT THEME (Default) */
                        .fi-body.fi-simple-layout {
                            background-color: #f8fafc !important;
                            background-image: radial-gradient(circle at top left, rgba(59, 130, 246, 0.04), transparent 45%),
                                              radial-gradient(circle at bottom right, rgba(139, 92, 246, 0.04), transparent 45%) !important;
                            color: #0f172a !important;
                        }
                        
                        /* Light theme card container */
                        .fi-simple-layout main,
                        .fi-simple-main,
                        .fi-simple-main-ctn {
                            background-color: rgba(255, 255, 255, 0.8) !important;
                            backdrop-filter: blur(12px) !important;
                            -webkit-backdrop-filter: blur(12px) !important;
                            border: 1px solid rgba(226, 232, 240, 0.8) !important;
                            border-radius: 1rem !important;
                            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
                        }
                        
                        /* Light theme labels and texts */
                        .fi-simple-layout label,
                        .fi-simple-layout .fi-fo-field-label,
                        .fi-simple-layout .text-sm,
                        .fi-simple-layout span {
                            color: #475569 !important; /* Slate-600 */
                        }
                        
                        .fi-simple-layout h1,
                        .fi-simple-layout h2,
                        .fi-simple-layout .fi-simple-header-title {
                            color: #0f172a !important; /* Slate-900 */
                        }
                        
                        /* Light theme inputs */
                        .fi-simple-layout input {
                            background-color: #ffffff !important;
                            border: 1px solid #cbd5e1 !important; /* Slate-300 */
                            color: #0f172a !important;
                            border-radius: 0.5rem !important;
                        }
                        
                        .fi-simple-layout input:focus {
                            border-color: #3b82f6 !important;
                            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
                        }

                        /* Fix password toggle wrapper background */
                        .fi-simple-layout .fi-input-wrp,
                        .fi-simple-layout .fi-input-wrapper {
                            background-color: transparent !important;
                            border: none !important;
                            box-shadow: none !important;
                        }

                        .fi-simple-layout .fi-input-wrp button,
                        .fi-simple-layout .fi-input-wrapper button {
                            background-color: transparent !important;
                        }
                        
                        /* Submit Button */
                        .fi-simple-layout button[type="submit"],
                        .fi-simple-layout .fi-btn {
                            background-color: #2563eb !important;
                            color: #ffffff !important;
                            font-weight: 700 !important;
                            border-radius: 0.5rem !important;
                        }
                        
                        .fi-simple-layout button[type="submit"] span,
                        .fi-simple-layout .fi-btn span,
                        .dark .fi-simple-layout button[type="submit"] span,
                        .dark .fi-simple-layout .fi-btn span {
                            color: #ffffff !important;
                        }
                        
                        .fi-simple-layout button[type="submit"]:hover,
                        .fi-simple-layout .fi-btn:hover {
                            background-color: #1d4ed8 !important;
                        }
                        
                        /* Muted links and helpers */
                        .fi-simple-layout a {
                            color: #2563eb !important;
                        }
                        .fi-simple-layout a:hover {
                            color: #1d4ed8 !important;
                            text-decoration: underline !important;
                        }

                        /* DARK THEME (when html tag has .dark class) */
                        .dark .fi-body.fi-simple-layout {
                            background-color: #030712 !important;
                            background-image: radial-gradient(circle at top left, rgba(59, 130, 246, 0.05), transparent 45%),
                                              radial-gradient(circle at bottom right, rgba(139, 92, 246, 0.05), transparent 45%) !important;
                            color: #f3f4f6 !important;
                        }
                        
                        /* Dark theme card container */
                        .dark .fi-simple-layout main,
                        .dark .fi-simple-main,
                        .dark .fi-simple-main-ctn {
                            background-color: #0b0f19 !important;
                            border: 1px solid #1e293b !important;
                            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7) !important;
                        }
                        
                        /* Dark theme labels and texts */
                        .dark .fi-simple-layout label,
                        .dark .fi-simple-layout .fi-fo-field-label,
                        .dark .fi-simple-layout .text-sm,
                        .dark .fi-simple-layout span {
                            color: #cbd5e1 !important; /* Slate-300 */
                        }
                        
                        .dark .fi-simple-layout h1,
                        .dark .fi-simple-layout h2,
                        .dark .fi-simple-layout .fi-simple-header-title {
                            color: #f8fafc !important; /* Slate-50 */
                        }
                        
                        /* Dark theme inputs */
                        .dark .fi-simple-layout input {
                            background-color: #0f172a !important; /* Slate-900 */
                            border: 1px solid #334155 !important; /* Slate-700 */
                            color: #ffffff !important;
                        }
                        
                        .dark .fi-simple-layout input:focus {
                            border-color: #3b82f6 !important;
                        }
                        
                        /* Dark theme muted links and helpers */
                        .dark .fi-simple-layout a {
                            color: #60a5fa !important;
                        }
                        .dark .fi-simple-layout a:hover {
                            color: #3b82f6 !important;
                        }

                        /* Sidebar layout differentiation */
                        .fi-sidebar {
                            background-color: #f1f5f9 !important; /* Soft slate-100 sidebar */
                            border-right: 1px solid #cbd5e1 !important; /* Slate-300 border */
                        }
                        .dark .fi-sidebar {
                            background-color: #0b0f19 !important; /* Deeper slate background */
                            border-right: 1px solid #1e293b !important;
                        }
                        /* Active/Hover states for sidebar items */
                        .fi-sidebar-item-button:hover {
                            background-color: #eff6ff !important;
                        }
                        .dark .fi-sidebar-item-button:hover {
                            background-color: #1e293b !important;
                        }
                    </style>
                ')
            )
            ->brandName('THISAI Admin')
            ->brandLogo(fn () => asset('images/logo.png'))
            ->brandLogoHeight('5.5rem')
            ->favicon(asset('images/logo.png'))
            ->colors([
                'primary' => Color::Blue,
            ])
            ->darkMode(true)
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
