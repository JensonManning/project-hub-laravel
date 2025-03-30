<?php

namespace App\Http\Controllers;

use App\Models\PhaseRepo;
use App\Models\Project;
use App\Models\TaskTypeRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Get all phases
        $phases = PhaseRepo::orderBy('order')->get();
        
        // Get task types for filtering
        $taskTypes = TaskTypeRepo::orderBy('name')->get();
        
        // Get projects where the current user is a resource
        $user = Auth::user();
        $userProjects = Project::whereHas('resources', function ($query) use ($user) {
            $query->where('project_resource.user_id', $user->id);
        })
        ->with([
            'phases' => function ($query) {
                $query->orderBy('order');
            },
            'phaseTasks.phase',
            'phaseTasks.taskDefinition',
            'phaseTasks.taskDefinition.taskType',
        ])
        ->get();
        
        // Group projects by their current phase
        $projectsByPhase = [];
        
        foreach ($phases as $phase) {
            $projectsByPhase[$phase->id] = [
                'phase' => $phase,
                'projects' => []
            ];
        }
        
        foreach ($userProjects as $project) {
            // Determine the current phase of the project
            $currentPhase = $this->determineCurrentPhase($project);
            
            if ($currentPhase && isset($projectsByPhase[$currentPhase->id])) {
                $projectsByPhase[$currentPhase->id]['projects'][] = $project;
            }
        }
        
        return Inertia::render('Dashboard', [
            'phases' => $phases,
            'taskTypes' => $taskTypes,
            'projectsByPhase' => $projectsByPhase,
        ]);
    }
    
    /**
     * Determine the current phase of a project based on the current date
     */
    private function determineCurrentPhase($project)
    {
        $today = now()->startOfDay();
        
        foreach ($project->phases as $phase) {
            $startDate = \Carbon\Carbon::parse($phase->pivot->start_date)->startOfDay();
            $endDate = \Carbon\Carbon::parse($phase->pivot->end_date)->endOfDay();
            
            if ($today->between($startDate, $endDate)) {
                return $phase;
            }
        }
        
        // If no current phase found, return the first phase if the project hasn't started yet
        // or the last phase if the project has ended
        if ($project->phases->count() > 0) {
            $firstPhase = $project->phases->first();
            $lastPhase = $project->phases->last();
            
            $firstStartDate = \Carbon\Carbon::parse($firstPhase->pivot->start_date)->startOfDay();
            $lastEndDate = \Carbon\Carbon::parse($lastPhase->pivot->end_date)->endOfDay();
            
            if ($today->lt($firstStartDate)) {
                return $firstPhase; // Project hasn't started yet
            } else {
                return $lastPhase; // Project has ended
            }
        }
        
        return null;
    }
}
