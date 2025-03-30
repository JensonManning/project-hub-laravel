<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\PhaseRepo;
use App\Models\NotebookRepo;
use App\Models\ResourceRepo;
use App\Models\User;
use App\Models\ProjectResource;
use App\Models\TaskRepo;
use App\Models\TaskTypeRepo;
use App\Models\ProjectPhaseTask;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::with(['phases', 'notebooks', 'resources'])->get();
        $phases = PhaseRepo::orderBy('order')->get();
        $notebooks = NotebookRepo::orderBy('name')->get();
        $resources = ResourceRepo::orderBy('name')->get();
        $users = User::where('approval_status', 'approved')->orderBy('name')->get();
        $tasks = TaskRepo::orderBy('name')->get();
        $taskTypes = TaskTypeRepo::orderBy('name')->get();

        return Inertia::render('project/Index', [
            'projects' => $projects,
            'phases' => $phases,
            'notebooks' => $notebooks,
            'resources' => $resources,
            'users' => $users,
            'tasks' => $tasks,
            'taskTypes' => $taskTypes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'shortcode' => ['required', 'string', 'max:7', 'unique:projects', 'regex:/^[A-Z]{4}\d{2}[A-Z]$/'],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['upcoming', 'active', 'completed', 'on_hold', 'cancelled', 'sale_pending', 'delayed'])],
            'phase_ids' => 'required|array',
            'phase_ids.*' => 'exists:phase_repos,id',
            'phase_dates' => 'required|array',
            'phase_dates.*.id' => 'exists:phase_repos,id',
            'phase_dates.*.start_date' => 'required|date',
            'phase_dates.*.end_date' => 'required|date|after_or_equal:phase_dates.*.start_date',
            'notebook_ids' => 'nullable|array',
            'notebook_ids.*' => 'exists:notebook_repos,id',
            'resources' => 'nullable|array',
            'resources.*.resource_id' => 'nullable|exists:resource_repos,id',
            'resources.*.user_id' => 'nullable|exists:users,id',
            'task_ids' => 'nullable|array',
            'task_ids.*' => 'exists:task_repos,id',
        ]);

        // Create the project
        $project = Project::create([
            'name' => $validated['name'],
            'shortcode' => $validated['shortcode'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'description' => $validated['description'],
            'status' => $validated['status'],
        ]);

        // Attach phases with dates
        foreach ($validated['phase_dates'] as $index => $phaseDate) {
            $project->phases()->attach($phaseDate['id'], [
                'start_date' => $phaseDate['start_date'],
                'end_date' => $phaseDate['end_date'],
                'order' => $index + 1,
            ]);
        }

        // Attach notebooks
        if (isset($validated['notebook_ids'])) {
            $project->notebooks()->attach($validated['notebook_ids']);
        }

        // Attach resources
        if (isset($validated['resources'])) {
            foreach ($validated['resources'] as $resource) {
                if ($resource['resource_id'] && $resource['user_id']) {
                    ProjectResource::create([
                        'project_id' => $project->id,
                        'resource_repo_id' => $resource['resource_id'],
                        'user_id' => $resource['user_id'],
                    ]);
                }
            }
        }

        // Attach tasks to phases
        if (isset($validated['task_ids']) && !empty($validated['task_ids'])) {
            $tasks = TaskRepo::whereIn('id', $validated['task_ids'])->get();
            
            foreach ($tasks as $task) {
                // Find the phase this task belongs to
                $phase = $project->phases()->where('phase_repos.id', $task->phase_repo_id)->first();
                
                if ($phase) {
                    // Get the phase pivot data to get start and end dates
                    $phasePivot = $phase->pivot;
                    
                    // Create the project phase task record
                    ProjectPhaseTask::create([
                        'project_id' => $project->id,
                        'phase_repo_id' => $task->phase_repo_id,
                        'task_repo_id' => $task->id,
                        'start_date' => $phasePivot->start_date,
                        'end_date' => $phasePivot->end_date,
                        'status' => 'pending',
                    ]);
                }
            }
        }

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $project->load([
            'phases', 
            'notebooks', 
            'resources.resourceType', 
            'resources.user',
            'phaseTasks.phase',
            'phaseTasks.taskDefinition'
        ]);
        
        return Inertia::render('project/Show', [
            'project' => $project,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'shortcode' => ['required', 'string', 'max:7', Rule::unique('projects')->ignore($project->id), 'regex:/^[A-Z]{4}\d{2}[A-Z]$/'],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['upcoming', 'active', 'completed', 'on_hold', 'cancelled', 'sale_pending', 'delayed'])],
            'phase_ids' => 'required|array',
            'phase_ids.*' => 'exists:phase_repos,id',
            'phase_dates' => 'required|array',
            'phase_dates.*.id' => 'exists:phase_repos,id',
            'phase_dates.*.start_date' => 'required|date',
            'phase_dates.*.end_date' => 'required|date|after_or_equal:phase_dates.*.start_date',
            'notebook_ids' => 'nullable|array',
            'notebook_ids.*' => 'exists:notebook_repos,id',
            'resources' => 'nullable|array',
            'resources.*.resource_id' => 'nullable|exists:resource_repos,id',
            'resources.*.user_id' => 'nullable|exists:users,id',
            'task_ids' => 'nullable|array',
            'task_ids.*' => 'exists:task_repos,id',
        ]);

        // Update the project
        $project->update([
            'name' => $validated['name'],
            'shortcode' => $validated['shortcode'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'description' => $validated['description'],
            'status' => $validated['status'],
        ]);

        // Sync phases with dates
        $project->phases()->detach();
        foreach ($validated['phase_dates'] as $index => $phaseDate) {
            $project->phases()->attach($phaseDate['id'], [
                'start_date' => $phaseDate['start_date'],
                'end_date' => $phaseDate['end_date'],
                'order' => $index + 1,
            ]);
        }

        // Sync notebooks
        if (isset($validated['notebook_ids'])) {
            $project->notebooks()->sync($validated['notebook_ids']);
        } else {
            $project->notebooks()->detach();
        }

        // Sync resources
        $project->resources()->delete();
        if (isset($validated['resources'])) {
            foreach ($validated['resources'] as $resource) {
                if ($resource['resource_id'] && $resource['user_id']) {
                    ProjectResource::create([
                        'project_id' => $project->id,
                        'resource_repo_id' => $resource['resource_id'],
                        'user_id' => $resource['user_id'],
                    ]);
                }
            }
        }

        // Sync tasks to phases
        $project->phaseTasks()->delete();
        if (isset($validated['task_ids']) && !empty($validated['task_ids'])) {
            $tasks = TaskRepo::whereIn('id', $validated['task_ids'])->get();
            
            foreach ($tasks as $task) {
                // Find the phase this task belongs to
                $phase = $project->phases()->where('phase_repos.id', $task->phase_repo_id)->first();
                
                if ($phase) {
                    // Get the phase pivot data to get start and end dates
                    $phasePivot = $phase->pivot;
                    
                    // Create the project phase task record
                    ProjectPhaseTask::create([
                        'project_id' => $project->id,
                        'phase_repo_id' => $task->phase_repo_id,
                        'task_repo_id' => $task->id,
                        'start_date' => $phasePivot->start_date,
                        'end_date' => $phasePivot->end_date,
                        'status' => 'pending',
                    ]);
                }
            }
        }

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}
