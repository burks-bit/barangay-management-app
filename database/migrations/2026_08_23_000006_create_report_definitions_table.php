<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('dataset', ['service_requests', 'complaints', 'assistance_requests']);
            $table->string('group_by'); // status | type | priority | category | month | source
            $table->json('filters')->nullable(); // { statuses: [], from: '', to: '', secondary_id: null }
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['dataset', 'group_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_definitions');
    }
};