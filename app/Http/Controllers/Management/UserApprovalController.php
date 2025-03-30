<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserApprovalController extends Controller
{
    /**
     * Display a listing of users pending approval.
     */
    public function index()
    {
        return Inertia::render('management/UserApproval', [
            'pendingUsers' => User::where('approval_status', 'pending')->get(),
            'approvedUsers' => User::where('approval_status', 'approved')->get(),
            'rejectedUsers' => User::where('approval_status', 'rejected')->get(),
            'roles' => Role::all(),
        ]);
    }

    /**
     * Approve a user.
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($id);
        $user->approval_status = 'approved';
        $user->role_id = $request->role_id;
        $user->save();

        return redirect()->back()->with('success', 'User approved successfully.');
    }

    /**
     * Reject a user.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($id);
        $user->approval_status = 'rejected';
        $user->rejection_reason = $request->rejection_reason;
        $user->save();

        return redirect()->back()->with('success', 'User rejected successfully.');
    }
}
