<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds 3,000 virtual students for load testing.
 * Each student gets a predictable email (loadtest_0001@thisai.com ... loadtest_3000@thisai.com)
 * and password ("password") for k6 to authenticate with.
 *
 * Usage: docker compose exec app php artisan db:seed --class=LoadTestSeeder
 */
class LoadTestSeeder extends Seeder
{
    protected const TOTAL_STUDENTS = 10000;
    protected const BATCH_SIZE = 1000;

    public function run(): void
    {
        $this->command->info("Seeding {self::TOTAL_STUDENTS} load test students...");

        $hashedPassword = Hash::make('password');
        $now = now();
        $batches = [];
        $batch = [];

        for ($i = 1; $i <= self::TOTAL_STUDENTS; $i++) {
            $paddedId = str_pad($i, 4, '0', STR_PAD_LEFT);

            $batch[] = [
                'name' => "Load Test Student {$paddedId}",
                'email' => "loadtest_{$paddedId}@thisai.com",
                'password' => $hashedPassword,
                'role' => UserRole::STUDENT->value,
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= self::BATCH_SIZE) {
                $batches[] = $batch;
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $batches[] = $batch;
        }

        $bar = $this->command->getOutput()->createProgressBar(count($batches));
        $bar->start();

        foreach ($batches as $batchData) {
            // Use upsert to avoid duplicates on re-run
            User::upsert(
                $batchData,
                ['email'],  // unique key
                ['name', 'password', 'role', 'is_active', 'updated_at']  // columns to update
            );
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("Successfully seeded {self::TOTAL_STUDENTS} load test students.");
    }
}
