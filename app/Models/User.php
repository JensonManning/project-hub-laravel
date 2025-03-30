<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;
use App\Models\Message;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'approval_status',
        'rejection_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'approval_status' => 'string',
        ];
    }
    
    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get all project resources assigned to the user.
     */
    public function projectResources(): HasMany
    {
        return $this->hasMany('App\Models\ProjectResource', 'user_id');
    }

    /**
     * Check if the user is approved.
     */
    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }
    
    /**
     * Check if the user is pending approval.
     */
    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }
    
    /**
     * Check if the user is rejected.
     */
    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }
    
    /**
     * Get the messages sent by the user.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
    
    /**
     * Get the messages received by the user.
     */
    public function receivedMessages()
    {
        return $this->belongsToMany(Message::class, 'message_recipients')
            ->withPivot('read', 'read_at')
            ->withTimestamps();
    }
    
    /**
     * Get unread messages for the user.
     */
    public function unreadMessages()
    {
        return $this->receivedMessages()->wherePivot('read', false);
    }
}
