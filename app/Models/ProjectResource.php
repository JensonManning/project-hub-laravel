<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Project;
use App\Models\ResourceRepo;
use App\Models\User;

class ProjectResource extends Model
{
    protected $table = 'project_resource';

    protected $fillable = [
        'project_id',
        'resource_repo_id',
        'user_id',
        'notes',
    ];

    /**
     * Get the project that owns the resource.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the resource type.
     */
    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceRepo::class, 'resource_repo_id');
    }

    /**
     * Get the assigned user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
