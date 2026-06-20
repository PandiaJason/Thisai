<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'bookmarkable_type' => ['required', 'string'],
            'bookmarkable_id' => ['required', 'integer'],
        ]);

        // Map short names to full namespaces for security/simplicity
        $typeMapping = [
            'video' => \App\Models\Video::class,
            'current_affairs' => \App\Models\CurrentAffairs::class,
            'exam' => \App\Models\Exam::class,
        ];

        $shortType = $request->bookmarkable_type;
        if (!array_key_exists($shortType, $typeMapping)) {
            return response()->json(['error' => 'Invalid bookmark type.'], 400);
        }

        $modelClass = $typeMapping[$shortType];
        $user = Auth::user();

        // Check if model exists
        $record = $modelClass::find($request->bookmarkable_id);
        if (!$record) {
            return response()->json(['error' => 'Record not found.'], 404);
        }

        $existing = Bookmark::where('user_id', $user->id)
            ->where('bookmarkable_type', $modelClass)
            ->where('bookmarkable_id', $request->bookmarkable_id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'bookmarked' => false,
                'message' => 'Bookmark removed.'
            ]);
        }

        Bookmark::create([
            'user_id' => $user->id,
            'bookmarkable_type' => $modelClass,
            'bookmarkable_id' => $request->bookmarkable_id,
        ]);

        return response()->json([
            'bookmarked' => true,
            'message' => 'Bookmark saved successfully.'
        ]);
    }
}
