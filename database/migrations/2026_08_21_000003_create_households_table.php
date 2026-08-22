<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->string('household_code')->unique();
            $table->string('address');
            $table->foreignId('purok_id')->constrained()->cascadeOnDelete();
            $table->string('contact_number')->nullable();
            $table->unsignedBigInteger('head_of_family_id')->nullable();
            $table->json('vulnerability_indicators')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['purok_id', 'address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};