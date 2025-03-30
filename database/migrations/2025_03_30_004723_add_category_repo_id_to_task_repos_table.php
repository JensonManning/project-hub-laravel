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
            $table->foreignId('category_repo_id')->after('phase_repo_id')->constrained('category_repos')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_repos', function (Blueprint $table) {
            $table->dropForeign(['category_repo_id']);
            $table->dropColumn('category_repo_id');
        });
    }
};
