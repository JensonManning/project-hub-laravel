<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Management\RoleManagementController;
use App\Http\Controllers\Management\UserApprovalController;
use App\Http\Controllers\Management\UserManagementController;
use App\Http\Controllers\Repo\CategoryRepoController;
use App\Http\Controllers\Repo\NotebookRepoController;
use App\Http\Controllers\Repo\PhaseRepoController;
use App\Http\Controllers\Repo\ResourceRepoController;
use App\Http\Controllers\Repo\TaskRepoController;
use App\Http\Controllers\Repo\TaskTypeRepoController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;

Route::get('/', function () {
    return Inertia::render('landing/Landing');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'approved'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    // Management routes
    Route::resource('management/roles', RoleManagementController::class)->names([
        'index' => 'management.roles',
        'store' => 'management.roles.store',
        'update' => 'management.roles.update',
        'destroy' => 'management.roles.destroy',
    ]);
    
    // Update user role
    Route::put('management/users/{user}/role', [RoleManagementController::class, 'updateUserRole'])
        ->name('management.users.update-role');
        
    // User approval routes
    Route::get('management/user-approvals', [UserApprovalController::class, 'index'])
        ->name('management.user-approvals');
    Route::post('management/user-approvals/{user}/approve', [UserApprovalController::class, 'approve'])
        ->name('management.user-approvals.approve');
    Route::post('management/user-approvals/{user}/reject', [UserApprovalController::class, 'reject'])
        ->name('management.user-approvals.reject');
        
    // User management routes
    Route::get('management/users', [UserManagementController::class, 'index'])
        ->name('management.users');
    Route::put('management/users/{user}', [UserManagementController::class, 'update'])
        ->name('management.users.update');
    Route::delete('management/users/{user}', [UserManagementController::class, 'destroy'])
        ->name('management.users.destroy');
});

// Repo routes
Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::resource('repo/phases', PhaseRepoController::class)->names([
        'index' => 'repo.phases',
        'store' => 'repo.phases.store',
        'update' => 'repo.phases.update',
        'destroy' => 'repo.phases.destroy',
    ]);
});

Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::resource('repo/notebooks', NotebookRepoController::class)->names([
        'index' => 'repo.notebooks',
        'store' => 'repo.notebooks.store',
        'update' => 'repo.notebooks.update',
        'destroy' => 'repo.notebooks.destroy',
    ]);
});

Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::resource('repo/resources', ResourceRepoController::class)->names([
        'index' => 'repo.resources',
        'store' => 'repo.resources.store',
        'update' => 'repo.resources.update',
        'destroy' => 'repo.resources.destroy',
    ]);
});

Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::resource('repo/categories', CategoryRepoController::class)->names([
        'index' => 'repo.categories',
        'store' => 'repo.categories.store',
        'update' => 'repo.categories.update',
        'destroy' => 'repo.categories.destroy',
    ]);
});

Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::resource('repo/task-types', TaskTypeRepoController::class)->names([
        'index' => 'repo.task-types',
        'store' => 'repo.task-types.store',
        'update' => 'repo.task-types.update',
        'destroy' => 'repo.task-types.destroy',
    ]);
});

Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::resource('repo/tasks', TaskRepoController::class)->names([
        'index' => 'repo.tasks',
        'store' => 'repo.tasks.store',
        'update' => 'repo.tasks.update',
        'destroy' => 'repo.tasks.destroy',
    ]);
});

// Project routes
Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::get('projects/manage', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
});

// Task routes
Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::put('tasks/{task}/complete', [TaskController::class, 'markCompleted'])->name('tasks.complete');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
