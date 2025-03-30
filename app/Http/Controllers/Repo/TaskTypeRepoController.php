<?php

namespace App\Http\Controllers\Repo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\TaskTypeRepo;
use Illuminate\Support\Facades\Validator;

class TaskTypeRepoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('repo/TaskTypeRepo', [
            'taskTypes' => TaskTypeRepo::all()
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
        ]);

        TaskTypeRepo::create($validated);

        return redirect()->route('repo.task-types');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $taskType = TaskTypeRepo::findOrFail($id);
        
        return Inertia::render('repo/TaskTypeRepo', [
            'taskType' => $taskType
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
        $taskType = TaskTypeRepo::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $taskType->update($validated);

        return redirect()->route('repo.task-types');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $taskType = TaskTypeRepo::findOrFail($id);
        $taskType->delete();

        return redirect()->route('repo.task-types');
    }
}
