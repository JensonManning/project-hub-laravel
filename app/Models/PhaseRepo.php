<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Project;
use App\Models\TaskRepo;
use App\Models\ProjectPhaseTask;

class PhaseRepo extends Model
{
    protected $fillable = [
        'name',
        'description',
        'order',
        'color',
    ];
    
    /**
     * The projects that belong to the phase.
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_phase')
            ->withPivot('order', 'start_date', 'end_date')
            ->withTimestamps();
    }

    /**
     * Get the tasks that belong to this phase.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(TaskRepo::class, 'phase_repo_id');
    }

    /**
     * Get the project-specific tasks for this phase.
     */
    public function projectTasks(): HasMany
    {
        return $this->hasMany(ProjectPhaseTask::class, 'phase_repo_id');
    }
}
