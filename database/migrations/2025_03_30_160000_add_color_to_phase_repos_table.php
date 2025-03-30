<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('phase_repos', function (Blueprint $table) {
            $table->string('color')->nullable()->after('order');
        });
        
        // Add default colors to existing phases
        $colors = [
            '#3b82f6', // Blue
            '#10b981', // Green
            '#f59e0b', // Amber
            '#8b5cf6', // Purple
            '#ef4444', // Red
            '#06b6d4', // Cyan
        ];
        
        $phases = DB::table('phase_repos')->orderBy('order')->get();
        
        foreach ($phases as $index => $phase) {
            $colorIndex = $index % count($colors);
            DB::table('phase_repos')
                ->where('id', $phase->id)
                ->update(['color' => $colors[$colorIndex]]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phase_repos', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
