<?php

namespace App\Http\Controllers\Repo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\NotebookRepo;
use Illuminate\Support\Facades\Validator;

class NotebookRepoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('repo/NotebookRepo', [
            'notebooks' => NotebookRepo::all()
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
            'content' => 'required|string',
        ]);

        NotebookRepo::create($validated);

        return redirect()->route('repo.notebooks');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $notebook = NotebookRepo::findOrFail($id);
        
        return Inertia::render('repo/NotebookRepo', [
            'notebook' => $notebook
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
        $notebook = NotebookRepo::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|string',
        ]);

        $notebook->update($validated);

        return redirect()->route('repo.notebooks');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $notebook = NotebookRepo::findOrFail($id);
        $notebook->delete();

        return redirect()->route('repo.notebooks');
    }
}
