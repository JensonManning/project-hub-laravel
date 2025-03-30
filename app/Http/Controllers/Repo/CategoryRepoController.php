<?php

namespace App\Http\Controllers\Repo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\CategoryRepo;
use Illuminate\Support\Facades\Validator;

class CategoryRepoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('repo/CategoryRepo', [
            'categories' => CategoryRepo::all()
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

        CategoryRepo::create($validated);

        return redirect()->route('repo.categories');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = CategoryRepo::findOrFail($id);
        
        return Inertia::render('repo/CategoryRepo', [
            'category' => $category
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
        $category = CategoryRepo::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $category->update($validated);

        return redirect()->route('repo.categories');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = CategoryRepo::findOrFail($id);
        $category->delete();

        return redirect()->route('repo.categories');
    }
}
