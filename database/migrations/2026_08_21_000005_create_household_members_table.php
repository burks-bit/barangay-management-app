<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('household_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_profile_id')->constrained('member_profiles')->cascadeOnDelete();
            $table->enum('relationship', ['head', 'spouse', 'child', 'parent', 'sibling', 'relative', 'other'])->default('other');
            $table->boolean('is_head')->default(false);
            $table->timestamps();

            $table->unique(['household_id', 'member_profile_id']);
            $table->index('member_profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_members');
    }
};