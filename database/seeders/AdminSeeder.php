<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\StudentProfile;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Batch
        $batch = Batch::updateOrCreate(
            ['name' => 'IAS 2026 Batch'],
            [
                'description' => 'Target UPSC CSE Prelims and Mains 2026.',
                'year' => 2026,
                'is_active' => true,
            ]
        );

        // 2. Create Super Admin
        User::updateOrCreate(
            ['email' => 'admin@thisai.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::SUPER_ADMIN,
                'is_active' => true,
            ]
        );

        // 3. Create Faculty
        User::updateOrCreate(
            ['email' => 'faculty@thisai.com'],
            [
                'name' => 'Dr. Ramesh Kumar',
                'phone' => '9876543210',
                'password' => Hash::make('password'),
                'role' => UserRole::FACULTY,
                'is_active' => true,
            ]
        );

        // 4. Create Student
        $student = User::updateOrCreate(
            ['email' => 'student@thisai.com'],
            [
                'name' => 'Vijay Sharma',
                'phone' => '8765432109',
                'password' => Hash::make('password'),
                'role' => UserRole::STUDENT,
                'is_active' => true,
            ]
        );

        // Link student profile
        StudentProfile::updateOrCreate(
            ['enrollment_number' => 'THISAI-2026-0001'],
            [
                'user_id' => $student->id,
                'date_of_birth' => '2001-08-15',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
                'target_exam' => 'UPSC CSE',
                'target_year' => 2026,
                'batch_id' => $batch->id,
            ]
        );
    }
}
