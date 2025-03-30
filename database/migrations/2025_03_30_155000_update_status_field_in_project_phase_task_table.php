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
        Schema::table('project_phase_task', function (Blueprint $table) {
            // First check if the column exists
            if (Schema::hasColumn('project_phase_task', 'status')) {
                // Change the status column to enum with the specified values
                $table->string('status')->default('upcoming')->change();
            } else {
                // Create the status column if it doesn't exist
                $table->string('status')->default('upcoming');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to drop the status column as it might contain important data
        // Just revert to a simple string if needed
        Schema::table('project_phase_task', function (Blueprint $table) {
            if (Schema::hasColumn('project_phase_task', 'status')) {
                $table->string('status')->change();
            }
        });
    }
};
