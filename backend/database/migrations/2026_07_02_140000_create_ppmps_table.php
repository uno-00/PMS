<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppmps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fiscal_year_id');
            $table->uuid('annual_procurement_plan_id')->nullable();
            $table->uuid('division_id');
            $table->string('ppmp_type')->default('regular'); // regular, supplemental, amended
            $table->uuid('parent_id')->nullable(); // originating PPMP for supplemental/amended
            $table->unsignedInteger('revision_number')->default(1);
            $table->string('control_no')->nullable()->unique();
            $table->string('title');
            $table->decimal('total_abc', 18, 2)->default(0);
            $table->string('status')->default('draft');
            $table->uuid('prepared_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->uuid('division_chief_by')->nullable();
            $table->timestamp('division_chief_at')->nullable();
            $table->uuid('planning_by')->nullable();
            $table->timestamp('planning_at')->nullable();
            $table->uuid('budget_by')->nullable();
            $table->timestamp('budget_at')->nullable();
            $table->uuid('bac_by')->nullable();
            $table->timestamp('bac_at')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->uuid('locked_by')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fiscal_year_id')->references('id')->on('fiscal_years')->cascadeOnDelete();
            $table->foreign('annual_procurement_plan_id')->references('id')->on('annual_procurement_plans')->nullOnDelete();
            $table->foreign('division_id')->references('id')->on('divisions')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('ppmps')->nullOnDelete();
            $table->foreign('prepared_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('division_chief_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('planning_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('budget_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('bac_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('locked_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('ppmp_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ppmp_id');
            $table->unsignedInteger('item_no')->default(0);
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->text('specification')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('quantity', 14, 2)->default(0);
            $table->decimal('estimated_unit_cost', 18, 2)->default(0);
            $table->decimal('abc', 18, 2)->default(0);
            $table->date('schedule_start')->nullable();
            $table->date('schedule_end')->nullable();
            $table->uuid('mode_of_procurement_id')->nullable();
            $table->uuid('fund_source_id')->nullable();
            $table->uuid('pap_id')->nullable();
            $table->uuid('uacs_code_id')->nullable();
            $table->uuid('budget_allocation_id')->nullable();
            $table->decimal('utilized_amount', 18, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('ppmp_id')->references('id')->on('ppmps')->cascadeOnDelete();
            $table->foreign('mode_of_procurement_id')->references('id')->on('modes_of_procurement')->nullOnDelete();
            $table->foreign('fund_source_id')->references('id')->on('fund_sources')->nullOnDelete();
            $table->foreign('pap_id')->references('id')->on('paps')->nullOnDelete();
            $table->foreign('uacs_code_id')->references('id')->on('uacs_codes')->nullOnDelete();
            $table->foreign('budget_allocation_id')->references('id')->on('budget_allocations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppmp_items');
        Schema::dropIfExists('ppmps');
    }
};
