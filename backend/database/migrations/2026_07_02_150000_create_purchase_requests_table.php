<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('pr_no')->unique();
            $table->uuid('fiscal_year_id');
            $table->uuid('division_id');
            $table->uuid('ppmp_id');
            $table->string('purpose');
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->string('status')->default('draft');
            $table->uuid('requested_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->uuid('division_chief_by')->nullable();
            $table->timestamp('division_chief_at')->nullable();
            $table->uuid('planning_by')->nullable();
            $table->timestamp('planning_at')->nullable();
            $table->uuid('budget_by')->nullable();
            $table->timestamp('budget_at')->nullable();
            $table->uuid('hope_by')->nullable();
            $table->timestamp('hope_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fiscal_year_id')->references('id')->on('fiscal_years')->cascadeOnDelete();
            $table->foreign('division_id')->references('id')->on('divisions')->cascadeOnDelete();
            $table->foreign('ppmp_id')->references('id')->on('ppmps')->cascadeOnDelete();
            $table->foreign('requested_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('division_chief_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('planning_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('budget_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('hope_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('purchase_request_id');
            $table->uuid('ppmp_item_id');
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('quantity', 14, 2)->default(0);
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->decimal('amount', 18, 2)->default(0);
            $table->timestamps();

            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->cascadeOnDelete();
            $table->foreign('ppmp_item_id')->references('id')->on('ppmp_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
        Schema::dropIfExists('purchase_requests');
    }
};
