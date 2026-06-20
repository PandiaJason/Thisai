<?php

return [
    'name' => 'THISAI IAS Academy',
    'exam' => [
        'max_attempts_per_exam' => 1,
        'auto_submit_buffer_seconds' => 30,
    ],
    'live_telecast' => [
        'default_start_time' => '06:00',
        'default_end_time' => '07:00',
        'auto_delete_hour' => 18, // 6 PM
    ],
    'leaderboard' => [
        'cache_ttl' => 3600,
        'top_n' => 100,
    ],
];
