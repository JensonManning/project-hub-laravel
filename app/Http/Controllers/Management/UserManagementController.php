<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index()
    {
        return Inertia::render('management/UserManagement', [
            'users' => User::with('role')->get(),
            'roles' => Role::all(),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'role_id' => 'required|exists:roles,id',
            'approval_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'rejection_reason' => 'nullable|string|max:255',
        ]);
        
        // Only hash the password if it's provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            
            $validated['password'] = Hash::make($request->password);
        }
        
        // Handle rejection reason
        if ($validated['approval_status'] === 'rejected' && empty($validated['rejection_reason'])) {
            return back()->withErrors(['rejection_reason' => 'Rejection reason is required when rejecting a user.']);
        }
        
        // Clear rejection reason if not rejected
        if ($validated['approval_status'] !== 'rejected') {
            $validated['rejection_reason'] = null;
        }
        
        $user->update($validated);
        
        return redirect()->back()->with('success', 'User updated successfully.');
    }

    /**
     * Delete the specified user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting yourself
        if (request()->user()->id === (int)$user->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }
        
        $user->delete();
        
        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
