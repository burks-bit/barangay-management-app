<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('unit');
            $table->unsignedInteger('current_stock')->default(0);
            $table->unsignedInteger('reorder_level')->default(0);
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->integer('quantity');
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');
            $table->string('source')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index('inventory_item_id');
            $table->index('type');
        });

        Schema::create('relief_distribution_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_code')->unique();
            $table->string('name');
            $table->foreignId('calamity_id')->nullable()->constrained()->nullOnDelete();
            $table->string('location');
            $table->timestamp('distribution_date');
            $table->enum('status', ['planned', 'ongoing', 'completed', 'cancelled'])->default('planned');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'distribution_date']);
            $table->index('calamity_id');
        });

        Schema::create('relief_distribution_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relief_distribution_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['relief_distribution_event_id', 'inventory_item_id'], 'relief_dist_items_unique');
        });

        Schema::create('relief_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relief_distribution_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('household_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('member_profile_id')->constrained()->cascadeOnDelete();
            $table->string('assistance_category')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->string('signature_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('distributed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('relief_distribution_event_id');
            $table->index('member_profile_id');
            $table->index('household_id');
            $table->unique(['relief_distribution_event_id', 'member_profile_id', 'assistance_category'], 'unique_recipient_per_event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relief_recipients');
        Schema::dropIfExists('relief_distribution_items');
        Schema::dropIfExists('relief_distribution_events');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_items');
    }
};