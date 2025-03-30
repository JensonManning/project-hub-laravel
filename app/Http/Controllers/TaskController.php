<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\PhaseRepo;
use App\Models\TaskRepo;
use App\Models\TaskTypeRepo;
use App\Models\ProjectPhaseTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of all tasks across all projects.
     */
    public function index()
    {
        // Get projects where the current user is a resource
        $user = Auth::user();
        $projects = Project::whereHas('resources', function ($query) use ($user) {
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
        
        // Get all task types for filtering
        $taskTypes = TaskTypeRepo::orderBy('name')->get();
        
        return Inertia::render('task/Index', [
            'projects' => $projects,
            'taskTypes' => $taskTypes,
        ]);
    }
    
    /**
     * Mark a task as completed.
     */
    public function markCompleted(Request $request, ProjectPhaseTask $task)
    {
        $validated = $request->validate([
            'completed' => 'required|boolean',
            'completion_notes' => 'nullable|string',
        ]);
        
        $task->update([
            'completed' => $validated['completed'],
            'completion_date' => $validated['completed'] ? now() : null,
            'completion_notes' => $validated['completion_notes'],
            'completed_by' => $validated['completed'] ? Auth::id() : null,
        ]);
        
        return back()->with('success', 'Task status updated successfully');
    }
}
