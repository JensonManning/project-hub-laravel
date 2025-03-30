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
        Schema::table('task_repos', function (Blueprint $table) {
            $table->renameColumn('has_sub_task_repo', 'has_subtasks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_repos', function (Blueprint $table) {
            $table->renameColumn('has_subtasks', 'has_sub_task_repo');
        });
    }
};
