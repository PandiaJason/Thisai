<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['discussion_id', 'user_id', 'body', 'is_accepted', 'upvotes'])]
class DiscussionReply extends Model
{
    protected function casts(): array
    {
        return [
            'is_accepted' => 'boolean',
            'upvotes' => 'integer',
        ];
    }

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): MorphMany
    {
        return $this->morphMany(DiscussionVote::class, 'votable');
    }
}
