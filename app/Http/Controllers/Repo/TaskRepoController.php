<?php

namespace App\Http\Controllers\Repo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\TaskRepo;
use App\Models\SubTaskRepo;
use App\Models\PhaseRepo;
use App\Models\CategoryRepo;
use App\Models\TaskTypeRepo;
use App\Models\ResourceRepo;
use Illuminate\Support\Facades\DB;

class TaskRepoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('repo/TaskRepo', [
            'tasks' => TaskRepo::with(['phase', 'category', 'taskType', 'subtasks'])->get(),
            'phases' => PhaseRepo::all(),
            'categories' => CategoryRepo::all(),
            'taskTypes' => TaskTypeRepo::all(),
            'resources' => ResourceRepo::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'phase_repo_id' => 'required|exists:phase_repos,id',
            'category_repo_id' => 'required|exists:category_repos,id',
            'task_type_repo_id' => 'required|exists:task_type_repos,id',
            'resource_repo_id' => 'nullable|exists:resource_repos,id',
            'has_subtasks' => 'boolean',
            'subtasks' => 'array|nullable',
            'subtasks.*.name' => 'required|string|max:255',
            'subtasks.*.description' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $task = TaskRepo::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'phase_repo_id' => $validated['phase_repo_id'],
                'category_repo_id' => $validated['category_repo_id'],
                'task_type_repo_id' => $validated['task_type_repo_id'],
                'resource_repo_id' => $validated['resource_repo_id'] ?? null,
                'has_subtasks' => $validated['has_subtasks'] ?? false,
            ]);

            // Create subtasks if they exist
            if (isset($validated['subtasks']) && is_array($validated['subtasks'])) {
                foreach ($validated['subtasks'] as $subtaskData) {
                    SubTaskRepo::create([
                        'name' => $subtaskData['name'],
                        'description' => $subtaskData['description'],
                        'task_repo_id' => $task->id,
                    ]);
                }
            }

            DB::commit();
            
            return redirect()->route('repo.tasks');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = TaskRepo::with(['phase', 'category', 'taskType', 'subtasks'])->findOrFail($id);
        
        return Inertia::render('repo/TaskRepo', [
            'task' => $task,
            'phases' => PhaseRepo::all(),
            'categories' => CategoryRepo::all(),
            'taskTypes' => TaskTypeRepo::all(),
            'resources' => ResourceRepo::all(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $task = TaskRepo::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'phase_repo_id' => 'required|exists:phase_repos,id',
            'category_repo_id' => 'required|exists:category_repos,id',
            'task_type_repo_id' => 'required|exists:task_type_repos,id',
            'has_subtasks' => 'boolean',
            'subtasks' => 'array|nullable',
            'subtasks.*.id' => 'nullable|exists:sub_task_repos,id',
            'subtasks.*.name' => 'required|string|max:255',
            'subtasks.*.description' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $task->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'phase_repo_id' => $validated['phase_repo_id'],
                'category_repo_id' => $validated['category_repo_id'],
                'task_type_repo_id' => $validated['task_type_repo_id'],
                'has_subtasks' => $validated['has_subtasks'] ?? false,
            ]);

            // Handle subtasks
            if (isset($validated['subtasks']) && is_array($validated['subtasks'])) {
                // Get existing subtask IDs
                $existingSubtaskIds = $task->subtasks->pluck('id')->toArray();
                $updatedSubtaskIds = [];

                foreach ($validated['subtasks'] as $subtaskData) {
                    if (isset($subtaskData['id'])) {
                        // Update existing subtask
                        $subtask = SubTaskRepo::findOrFail($subtaskData['id']);
                        $subtask->update([
                            'name' => $subtaskData['name'],
                            'description' => $subtaskData['description'],
                        ]);
                        $updatedSubtaskIds[] = $subtask->id;
                    } else {
                        // Create new subtask
                        $subtask = SubTaskRepo::create([
                            'name' => $subtaskData['name'],
                            'description' => $subtaskData['description'],
                            'task_repo_id' => $task->id,
                        ]);
                        $updatedSubtaskIds[] = $subtask->id;
                    }
                }

                // Delete subtasks that were not included in the update
                $subtasksToDelete = array_diff($existingSubtaskIds, $updatedSubtaskIds);
                if (!empty($subtasksToDelete)) {
                    SubTaskRepo::whereIn('id', $subtasksToDelete)->delete();
                }
            } else {
                // If no subtasks were provided, delete all existing subtasks
                $task->subtasks()->delete();
            }

            DB::commit();
            
            return redirect()->route('repo.tasks');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = TaskRepo::findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            // Delete all subtasks first
            $task->subtasks()->delete();
            
            // Then delete the task
            $task->delete();
            
            DB::commit();
            
            return redirect()->route('repo.tasks');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
