<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangay_officials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barangay_profile_id')->constrained()->cascadeOnDelete();
            $table->enum('position', [
                'captain',
                'vice_captain',
                'kagawad',
                'secretary',
                'treasurer',
                'sangguniang_kabataan_chairperson',
                'barangay_tanod',
                'health_worker',
                'other',
            ])->default('other');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->enum('sex', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->string('committee')->nullable();
            $table->year('term_start');
            $table->year('term_end')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['barangay_profile_id', 'position', 'term_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangay_officials');
    }
};