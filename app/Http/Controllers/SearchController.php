<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        $results = [];
        if (filled($query)) {
            $results = $this->searchService->search($query);
        }

        return view('search.results', compact('results', 'query'));
    }
}
