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
            $table->boolean('completed')->default(false);
            $table->timestamp('completion_date')->nullable();
            $table->text('completion_notes')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_phase_task', function (Blueprint $table) {
            $table->dropForeign(['completed_by']);
            $table->dropColumn(['completed', 'completion_date', 'completion_notes', 'completed_by']);
        });
    }
};
