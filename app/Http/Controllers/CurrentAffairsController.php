<?php

namespace App\Http\Controllers;

use App\Models\CurrentAffairs;
use App\Models\Subject;
use App\Enums\CurrentAffairsType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CurrentAffairsController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::active()->get();
        
        $selectedDate = $request->filled('date') 
            ? Carbon::parse($request->date)->format('Y-m-d') 
            : Carbon::today()->format('Y-m-d');

        $query = CurrentAffairs::published()
            ->whereDate('publish_date', $selectedDate);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('subject')) {
            $query->where('subject_id', $request->subject);
        }

        $articles = $query->latest()->paginate(10);

        return view('current-affairs.index', compact('articles', 'subjects', 'selectedDate'));
    }

    public function show(CurrentAffairs $article)
    {
        if (!$article->is_published) {
            abort(404);
        }

        $article->load(['subject', 'author']);

        // Related articles
        $related = CurrentAffairs::published()
            ->where('id', '!=', $article->id)
            ->where('type', $article->type->value)
            ->limit(3)
            ->get();

        return view('current-affairs.show', compact('article', 'related'));
    }
}
