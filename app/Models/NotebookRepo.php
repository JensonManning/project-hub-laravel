<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Project;

class NotebookRepo extends Model
{
    protected $fillable = [
        'name',
        'description',
        'content',
    ];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_notebook')
            ->withTimestamps();
    }
}
