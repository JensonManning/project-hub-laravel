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
            // Drop the foreign key constraint
            $table->dropForeign(['resource_repo_id']);
            
            // Make the column nullable
            $table->unsignedBigInteger('resource_repo_id')->nullable()->change();
            
            // Add the foreign key back with nullable constraint
            $table->foreign('resource_repo_id')
                  ->references('id')
                  ->on('resource_repos')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_repos', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['resource_repo_id']);
            
            // Make the column non-nullable again
            $table->unsignedBigInteger('resource_repo_id')->nullable(false)->change();
            
            // Add back the original foreign key constraint
            $table->foreign('resource_repo_id')
                  ->references('id')
                  ->on('resource_repos')
                  ->onDelete('cascade');
        });
    }
};
