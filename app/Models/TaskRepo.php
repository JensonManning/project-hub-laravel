<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskRepo extends Model
{
    protected $fillable = [
        'name',
        'description',
        'phase_repo_id',
        'category_repo_id',
        'task_type_repo_id',
        'resource_repo_id',
        'has_subtasks',
    ];

    /**
     * Get the phase that this task belongs to.
     */
    public function phase(): BelongsTo
    {
        return $this->belongsTo(PhaseRepo::class, 'phase_repo_id');
    }

    /**
     * Get the category that this task belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryRepo::class, 'category_repo_id');
    }

    /**
     * Get the task type that this task belongs to.
     */
    public function taskType(): BelongsTo
    {
        return $this->belongsTo(TaskTypeRepo::class, 'task_type_repo_id');
    }

    /**
     * Get the resource that this task is assigned to.
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(ResourceRepo::class, 'resource_repo_id');
    }

    /**
     * Get the subtasks for this task.
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(SubTaskRepo::class, 'task_repo_id');
    }

    /**
     * Get the project-specific instances of this task.
     */
    public function projectTasks(): HasMany
    {
        return $this->hasMany(ProjectPhaseTask::class, 'task_repo_id');
    }
}
