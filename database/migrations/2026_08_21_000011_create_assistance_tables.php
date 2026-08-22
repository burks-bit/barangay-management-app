<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistance_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('assistance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('assistance_code')->unique();
            $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assistance_type_id')->constrained()->restrictOnDelete();
            $table->text('reason');
            $table->decimal('amount', 10, 2)->nullable();
            $table->enum('status', [
                'submitted', 'for_verification', 'under_assessment',
                'approved', 'rejected', 'for_release', 'released', 'cancelled'
            ])->default('submitted');
            $table->text('assessment')->nullable();
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assessed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->text('release_details')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'assistance_code']);
            $table->index('applicant_id');
            $table->index('assistance_type_id');
        });

        Schema::create('assistance_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assistance_request_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->timestamps();

            $table->index('assistance_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistance_attachments');
        Schema::dropIfExists('assistance_requests');
        Schema::dropIfExists('assistance_types');
    }
};