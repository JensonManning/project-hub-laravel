<?php

namespace App\Providers;

use App\Http\Middleware\EnsureUserIsApproved;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register the 'approved' middleware alias
        Route::aliasMiddleware('approved', EnsureUserIsApproved::class);
    }
}
