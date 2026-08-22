<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evacuation_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->unsignedInteger('capacity')->default(0);
            $table->unsignedInteger('current_occupancy')->default(0);
            $table->json('facilities')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_number')->nullable();
            $table->enum('status', ['available', 'occupied', 'full', 'closed'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });

        Schema::create('evacuation_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_code')->unique();
            $table->foreignId('calamity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evacuation_center_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'calamity_id']);
            $table->index('evacuation_center_id');
        });

        Schema::create('evacuation_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evacuation_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('household_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('member_profile_id')->constrained()->cascadeOnDelete();
            $table->timestamp('time_in');
            $table->timestamp('time_out')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('registered_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('evacuation_event_id', 'evac_reg_event_idx');
            $table->index('member_profile_id', 'evac_reg_profile_idx');
            $table->index('household_id', 'evac_reg_hh_idx');
            $table->index(['evacuation_event_id', 'member_profile_id'], 'evac_reg_event_profile_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evacuation_registrations');
        Schema::dropIfExists('evacuation_events');
        Schema::dropIfExists('evacuation_centers');
    }
};