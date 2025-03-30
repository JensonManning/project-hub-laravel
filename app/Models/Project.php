<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'shortcode',
        'start_date',
        'end_date',
        'description',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    
    /**
     * The phases that belong to the project.
     */
    public function phases(): BelongsToMany
    {
        return $this->belongsToMany(PhaseRepo::class, 'project_phase')
            ->withPivot('order', 'start_date', 'end_date')
            ->orderBy('order')
            ->withTimestamps();
    }

    public function notebooks(): BelongsToMany
    {
        return $this->belongsToMany(NotebookRepo::class, 'project_notebook')
            ->withTimestamps();
    }

    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(ResourceRepo::class, 'project_resource')
            ->withPivot('user_id', 'notes')
            ->withTimestamps();
    }

    /**
     * Get the tasks for this project, organized by phase.
     */
    public function phaseTasks(): HasMany
    {
        return $this->hasMany(ProjectPhaseTask::class);
    }
    
    /**
     * Get the messages for this project.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
