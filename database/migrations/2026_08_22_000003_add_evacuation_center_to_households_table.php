<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->foreignId('evacuation_center_id')->nullable()->after('head_of_family_id')->constrained('evacuation_centers')->nullOnDelete();
            $table->timestamp('evacuated_at')->nullable()->after('evacuation_center_id');
            $table->enum('evacuation_status', ['none', 'evacuated', 'returned'])->default('none')->after('evacuated_at');
        });
    }

    public function down(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->dropForeign(['evacuation_center_id']);
            $table->dropColumn(['evacuation_center_id', 'evacuated_at', 'evacuation_status']);
        });
    }
};