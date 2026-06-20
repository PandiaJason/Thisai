<?php

use App\Http\Controllers\Api\ExamApiController;
use App\Http\Controllers\Api\VideoProgressController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // We can use standard web session auth as fallback if Sanctum is not fully configured,
    // but in modern Laravel, standard session auth on API is standard when requests come from the same origin.
    // So we will support both or standard session auth since our frontend uses standard session cookies.
});

// API routes are now defined in routes/web.php to share web session state.
