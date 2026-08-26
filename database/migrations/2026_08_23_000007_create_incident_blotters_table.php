<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_blotters', function (Blueprint $table) {
            $table->id();
            $table->string('blotter_code')->unique();
            $table->foreignId('incident_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purok_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('entry_type', ['accident', 'animal_incident', 'disturbance', 'theft', 'dispute', 'property_damage', 'other'])->default('other');
            $table->string('title');
            $table->text('narrative');
            $table->string('location');
            $table->timestamp('incident_datetime')->useCurrent();
            $table->string('complainant_name')->nullable();
            $table->string('complainant_contact', 50)->nullable();
            $table->text('involved_persons')->nullable();
            $table->boolean('injuries_reported')->default(false);
            $table->text('actions_taken')->nullable();
            $table->enum('status', ['recorded', 'under_investigation', 'settled', 'referred', 'closed'])->default('recorded');
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('settled_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'entry_type']);
            $table->index('incident_datetime');
            $table->index('purok_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_blotters');
    }
};