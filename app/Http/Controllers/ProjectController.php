<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::orderBy('created_at', 'desc')->get();
        
        return Inertia::render('project/Index', [
            'projects' => $projects
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'shortcode' => [
                'required',
                'string',
                'max:7',
                'unique:projects',
                'regex:/^[A-Za-z]{4}[0-9]{2}[A-Za-z]{1}$/'
            ],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['upcoming', 'active', 'completed', 'on_hold', 'cancelled', 'sale_pending', 'delayed'])],
        ]);

        $project = Project::create($validated);

        return redirect()->route('project.index')->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return Inertia::render('project/Show', [
            'project' => $project
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'shortcode' => [
                'required',
                'string',
                'max:7',
                Rule::unique('projects')->ignore($project->id),
                'regex:/^[A-Za-z]{4}[0-9]{2}[A-Za-z]{1}$/'
            ],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['upcoming', 'active', 'completed', 'on_hold', 'cancelled', 'sale_pending', 'delayed'])],
        ]);

        $project->update($validated);

        return redirect()->route('project.index')->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('project.index')->with('success', 'Project deleted successfully.');
    }
}
