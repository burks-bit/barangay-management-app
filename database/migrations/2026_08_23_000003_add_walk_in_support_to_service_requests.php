<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the FK first so the column can be made nullable.
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['requester_id']);
        });

        Schema::table('service_requests', function (Blueprint $table) {
            // Walk-in requests may be encoded for residents without a user account,
            // so the requester is now optional.
            $table->foreignId('requester_id')->nullable()->change();
            $table->foreign('requester_id')->references('id')->on('users')->nullOnDelete();

            // The resident this request is for (works with or without an account).
            $table->foreignId('member_profile_id')->nullable()->after('requester_id')
                ->constrained('member_profiles')->nullOnDelete();

            // Distinguish online submissions from staff-encoded walk-in requests.
            $table->string('source', 20)->default('online')->after('description');

            // Staff member who encoded a walk-in request.
            $table->foreignId('created_by')->nullable()->after('assigned_to')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['requester_id']);
            $table->dropConstrainedForeignId('member_profile_id');
            $table->dropColumn('source');
            $table->dropConstrainedForeignId('created_by');
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->foreignId('requester_id')->nullable(false)->change();
            $table->foreign('requester_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};