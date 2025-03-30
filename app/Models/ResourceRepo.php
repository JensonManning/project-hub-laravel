<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceRepo extends Model
{
    protected $fillable = [
        'name',
        'description',
        'resource_type_id',
    ];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_resource')
            ->withPivot('user_id', 'notes')
            ->withTimestamps();
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'project_resource', 'resource_repo_id', 'user_id');
    }
    
    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceTypeRepo::class, 'resource_type_id');
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
