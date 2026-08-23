<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->text('document_content')->nullable()->after('description');
            $table->foreignId('encoded_by')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('encoded_by')->constrained('users')->nullOnDelete();
            $table->timestamp('encoded_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['encoded_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['document_content', 'encoded_by', 'approved_by', 'encoded_at']);
        });
    }
};