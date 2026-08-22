<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calamities', function (Blueprint $table) {
            $table->id();
            $table->string('event_code')->unique();
            $table->string('name');
            $table->enum('type', ['typhoon', 'flood', 'earthquake', 'fire', 'landslide', 'storm_surge', 'other']);
            $table->text('description')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->enum('severity', ['low', 'moderate', 'high', 'severe', 'critical'])->default('moderate');
            $table->enum('status', ['reported', 'active', 'under_response', 'contained', 'resolved', 'archived'])->default('reported');
            $table->unsignedInteger('affected_households')->default(0);
            $table->unsignedInteger('affected_residents')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'type']);
            $table->index('started_at');
        });

        Schema::create('calamity_puroks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calamity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purok_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['calamity_id', 'purok_id']);
        });

        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('incident_code')->unique();
            $table->foreignId('calamity_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['flood', 'fire', 'earthquake', 'landslide', 'storm_surge', 'typhoon', 'accident', 'crime', 'other']);
            $table->string('location');
            $table->foreignId('purok_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description');
            $table->enum('severity', ['low', 'moderate', 'high', 'severe', 'critical'])->default('moderate');
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['reported', 'verified', 'under_response', 'contained', 'resolved', 'closed'])->default('reported');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('actions_taken')->nullable();
            $table->unsignedInteger('affected_households')->default(0);
            $table->unsignedInteger('affected_residents')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('incident_datetime')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'type']);
            $table->index('calamity_id');
            $table->index('purok_id');
            $table->index('reported_by');
        });

        Schema::create('incident_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->timestamps();

            $table->index('incident_id');
        });

        Schema::create('incident_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('incident_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_status_histories');
        Schema::dropIfExists('incident_attachments');
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('calamity_puroks');
        Schema::dropIfExists('calamities');
    }
};