<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->approval_status !== 'approved') {
            // If the user is authenticated but not approved
            Auth::logout();
            
            return redirect()->route('login')
                ->with('status', 'Your account is pending approval. You will be notified once your account is approved.');
        }

        return $next($request);
    }
}
