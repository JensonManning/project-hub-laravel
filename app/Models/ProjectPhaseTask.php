<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Project;
use App\Models\PhaseRepo;
use App\Models\TaskRepo;

class ProjectPhaseTask extends Model
{
    protected $table = 'project_phase_task';

    protected $fillable = [
        'project_id',
        'phase_repo_id',
        'task_repo_id',
        'start_date',
        'end_date',
        'status',
        'notes',
        'completed',
        'completion_date',
        'completion_notes',
        'completed_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'completion_date' => 'date',
        'completed' => 'boolean',
    ];

    /**
     * Get the project that owns the task.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the phase that owns the task.
     */
    public function phase(): BelongsTo
    {
        return $this->belongsTo(PhaseRepo::class, 'phase_repo_id');
    }

    /**
     * Get the task definition.
     */
    public function taskDefinition(): BelongsTo
    {
        return $this->belongsTo(TaskRepo::class, 'task_repo_id');
    }
}
