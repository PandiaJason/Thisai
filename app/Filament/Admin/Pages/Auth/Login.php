<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getHeading(): string|Htmlable
    {
        return 'THISAI Admin';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Sign in to access your administrative dashboard';
    }
}
