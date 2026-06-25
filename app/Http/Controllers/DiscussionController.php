<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\DiscussionVote;
use App\Models\Subject;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscussionController extends Controller
{
    public function index(Request $request)
    {
        $query = Discussion::with(['user', 'subject']);

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by resolution status
        if ($request->filled('is_resolved')) {
            $query->where('is_resolved', $request->is_resolved === 'yes');
        }

        // Sort
        $sort = $request->get('sort', 'recent');
        if ($sort === 'popular') {
            $query->orderByDesc('upvotes')->orderByDesc('reply_count');
        } else {
            $query->latest();
        }

        $discussions = $query->paginate(15)->withQueryString();
        $subjects = Subject::active()->orderBy('name')->get();

        return view('discussions.index', compact('discussions', 'subjects'));
    }

    public function show($id)
    {
        $discussion = Discussion::with(['user', 'subject', 'replies.user'])->findOrFail($id);

        return view('discussions.show', compact('discussion'));
    }

    public function create()
    {
        $subjects = Subject::active()->orderBy('name')->get();

        return view('discussions.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
            'subject_id' => 'required|exists:subjects,id',
            'question_id' => 'nullable|exists:questions,id',
        ]);

        $discussion = Discussion::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'body' => $validated['body'],
            'subject_id' => $validated['subject_id'],
            'question_id' => $validated['question_id'] ?? null,
        ]);

        return redirect()->route('discussions.show', $discussion->id)
            ->with('success', 'Your question has been posted successfully.');
    }

    public function reply($id, Request $request)
    {
        $discussion = Discussion::findOrFail($id);

        $validated = $request->validate([
            'body' => 'required|string|max:10000',
        ]);

        DiscussionReply::create([
            'discussion_id' => $discussion->id,
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        $discussion->increment('reply_count');

        return redirect()->route('discussions.show', $discussion->id)
            ->with('success', 'Your reply has been posted.');
    }

    public function vote($id, Request $request)
    {
        $type = $request->get('type', 'discussion'); // 'discussion' or 'reply'

        $existingVote = DiscussionVote::where('user_id', Auth::id())
            ->where('votable_type', $type === 'reply' ? DiscussionReply::class : Discussion::class)
            ->where('votable_id', $id)
            ->first();

        if ($existingVote) {
            $existingVote->delete();
            $delta = -1;
        } else {
            DiscussionVote::create([
                'user_id' => Auth::id(),
                'votable_type' => $type === 'reply' ? DiscussionReply::class : Discussion::class,
                'votable_id' => $id,
                'value' => 1,
            ]);
            $delta = 1;
        }

        // Update the upvote_count on the parent model
        if ($type === 'reply') {
            $model = DiscussionReply::findOrFail($id);
        } else {
            $model = Discussion::findOrFail($id);
        }
        $model->increment('upvotes', $delta);
        $model->refresh();

        return response()->json([
            'success' => true,
            'upvote_count' => $model->upvotes,
            'voted' => !$existingVote,
        ]);
    }

    public function resolve($id)
    {
        $discussion = Discussion::findOrFail($id);

        // Only the author or faculty can resolve
        $user = Auth::user();
        if ($discussion->user_id !== $user->id && $user->role !== UserRole::FACULTY) {
            abort(403, 'Only the author or faculty can resolve this discussion.');
        }

        $discussion->update(['is_resolved' => true]);

        return redirect()->route('discussions.show', $discussion->id)
            ->with('success', 'Discussion marked as resolved.');
    }
}
