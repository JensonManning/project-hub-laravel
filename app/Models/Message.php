<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Message extends Model
{
    protected $fillable = [
        'project_id',
        'sender_id',
        'title',
        'subject',
        'body',
        'is_thread_starter',
        'parent_id',
    ];

    /**
     * Get the project that the message belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who sent the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the parent message if this is a reply.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    /**
     * Get the replies to this message.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'parent_id');
    }

    /**
     * Get the recipients of this message.
     */
    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_recipients')
            ->withPivot('read', 'read_at')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include thread starter messages.
     */
    public function scopeThreadStarters($query)
    {
        return $query->where('is_thread_starter', true);
    }

    /**
     * Check if the message has been read by a specific user.
     */
    public function isReadBy(User $user): bool
    {
        return $this->recipients()->where('user_id', $user->id)->wherePivot('read', true)->exists();
    }

    /**
     * Mark the message as read by a specific user.
     */
    public function markAsReadBy(User $user): void
    {
        $this->recipients()->updateExistingPivot($user->id, [
            'read' => true,
            'read_at' => now(),
        ]);
    }
}
