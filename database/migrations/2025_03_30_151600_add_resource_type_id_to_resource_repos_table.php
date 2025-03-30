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
        Schema::table('resource_repos', function (Blueprint $table) {
            $table->foreignId('resource_type_id')->nullable()->constrained('resource_type_repos')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resource_repos', function (Blueprint $table) {
            $table->dropForeign(['resource_type_id']);
            $table->dropColumn('resource_type_id');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
