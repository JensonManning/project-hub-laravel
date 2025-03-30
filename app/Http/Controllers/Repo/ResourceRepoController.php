<?php

namespace App\Http\Controllers\Repo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ResourceRepo;
use Illuminate\Support\Facades\Validator;

class ResourceRepoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('repo/ResourceRepo', [
            'resources' => ResourceRepo::all()
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

        ResourceRepo::create($validated);

        return redirect()->route('repo.resources');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $resource = ResourceRepo::findOrFail($id);
        
        return Inertia::render('repo/ResourceRepo', [
            'resource' => $resource
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
        $resource = ResourceRepo::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $resource->update($validated);

        return redirect()->route('repo.resources');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $resource = ResourceRepo::findOrFail($id);
        $resource->delete();

        return redirect()->route('repo.resources');
    }
}
