<?php

namespace App\Console\Commands;

use App\Models\LiveTelecast;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanExpiredLiveTelecasts extends Command
{
    protected $signature = 'thisai:clean-live';

    protected $description = 'Automatically delete live telecasts after their evening deletion threshold (6:00 PM - 7:00 PM)';

    public function handle(): void
    {
        $now = Carbon::now();
        $this->info("Checking expired live telecasts at: " . $now->toDateTimeString());

        // Find active live telecasts where auto_delete_at is in the past
        $expired = LiveTelecast::where('is_active', true)
            ->where('status', '!=', 'deleted')
            ->where('auto_delete_at', '<=', $now)
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired telecasts found.');
            return;
        }

        foreach ($expired as $telecast) {
            $telecast->status = 'deleted';
            $telecast->is_active = false;
            $telecast->save();

            $this->info("Deleted live telecast: ID {$telecast->id} - '{$telecast->title}'");
            Log::info("Live telecast automatically deleted after evening expiration.", [
                'id' => $telecast->id,
                'title' => $telecast->title,
                'auto_delete_at' => $telecast->auto_delete_at->toDateTimeString(),
            ]);
        }

        $this->info('Cleanup complete.');
    }
}
