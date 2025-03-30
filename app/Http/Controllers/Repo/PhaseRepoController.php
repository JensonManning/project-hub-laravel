<?php

namespace App\Http\Controllers\Repo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PhaseRepo;
use Illuminate\Support\Facades\Validator;

class PhaseRepoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('repo/PhaseRepo', [
            'phases' => PhaseRepo::orderBy('order')->get()
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
            'order' => 'required|integer|min:1',
        ]);

        PhaseRepo::create($validated);

        return redirect()->route('repo.phases');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $phase = PhaseRepo::findOrFail($id);
        
        return Inertia::render('repo/PhaseRepo', [
            'phase' => $phase
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
        $phase = PhaseRepo::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'order' => 'required|integer|min:1',
        ]);

        $phase->update($validated);

        return redirect()->route('repo.phases');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $phase = PhaseRepo::findOrFail($id);
        $phase->delete();

        return redirect()->route('repo.phases');
    }
}
