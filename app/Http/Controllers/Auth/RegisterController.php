<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\Batch;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        $batches = Batch::active()->get();
        return view('auth.register', compact('batches'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'target_exam' => ['required', 'string'],
            'target_year' => ['required', 'integer', 'min:2024', 'max:2100'],
            'batch_id' => ['required', 'exists:batches,id'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => UserRole::STUDENT,
            'is_active' => true,
        ]);

        StudentProfile::create([
            'user_id' => $user->id,
            'target_exam' => $request->target_exam,
            'target_year' => $request->target_year,
            'batch_id' => $request->batch_id,
        ]);

        Auth::login($user);

        return redirect('/dashboard');
    }
}
