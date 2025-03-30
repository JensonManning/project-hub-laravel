<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageRecipient extends Model
{
    protected $table = 'message_recipients';
    
    protected $fillable = [
        'message_id',
        'user_id',
        'read',
        'read_at',
    ];
    
    protected $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime',
    ];
    
    /**
     * Get the message that this recipient belongs to.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
    
    /**
     * Get the user that is the recipient.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
