<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_repo_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_repo_id')->constrained('task_repos')->cascadeOnDelete();
            $table->foreignId('resource_repo_id')->constrained('resource_repos')->cascadeOnDelete();
            $table->timestamps();
            
            // Add a unique constraint to prevent duplicate entries
            $table->unique(['task_repo_id', 'resource_repo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_repo_resources');
    }
};
