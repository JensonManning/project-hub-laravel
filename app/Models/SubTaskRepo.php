<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubTaskRepo extends Model
{
    protected $fillable = [
        'name',
        'description',
        'task_repo_id',
    ];

    /**
     * Get the task that this subtask belongs to.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(TaskRepo::class, 'task_repo_id');
    }
}
