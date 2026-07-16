<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppmp_consolidations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
            $table->foreignUuid('parent_id')->nullable()->constrained('ppmp_consolidations')->nullOnDelete();
            $table->string('reference_no')->unique();
            $table->string('title');
            $table->string('document_type'); // indicative | final
            $table->string('status')->default('draft');
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->unsignedInteger('version_number')->default(1);
            $table->decimal('total_budget', 18, 2)->default(0);
            $table->json('validation_issues')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('planning_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('planning_at')->nullable();
            $table->foreignUuid('budget_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('budget_at')->nullable();
            $table->foreignUuid('accounting_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accounting_at')->nullable();
            $table->foreignUuid('bac_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('bac_at')->nullable();
            $table->foreignUuid('hope_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hope_at')->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignUuid('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ppmp_consolidation_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ppmp_consolidation_id')->constrained('ppmp_consolidations')->cascadeOnDelete();
            $table->foreignUuid('ppmp_id')->constrained('ppmps')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ppmp_consolidation_id', 'ppmp_id']);
        });

        Schema::create('ppmp_consolidation_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ppmp_consolidation_id')->constrained('ppmp_consolidations')->cascadeOnDelete();
            $table->foreignUuid('source_ppmp_id')->nullable()->constrained('ppmps')->nullOnDelete();
            $table->foreignUuid('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->string('group_key')->nullable();
            $table->json('source_ppmp_item_ids')->nullable();
            $table->unsignedInteger('item_no')->default(1);
            $table->string('expense_class')->nullable();
            $table->string('project_type')->nullable();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->text('specification')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('quantity', 18, 2)->default(0);
            $table->decimal('estimated_unit_cost', 18, 2)->default(0);
            $table->decimal('line_abc', 18, 2)->default(0);
            $table->foreignUuid('mode_of_procurement_id')->nullable()->constrained('modes_of_procurement')->nullOnDelete();
            $table->foreignUuid('fund_source_id')->nullable()->constrained('fund_sources')->nullOnDelete();
            $table->foreignUuid('pap_id')->nullable()->constrained('paps')->nullOnDelete();
            $table->foreignUuid('uacs_code_id')->nullable()->constrained('uacs_codes')->nullOnDelete();
            $table->date('schedule_start')->nullable();
            $table->date('schedule_end')->nullable();
            $table->string('pre_procurement_conference')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_merged')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ppmp_consolidation_bp2020_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ppmp_consolidation_id')->constrained('ppmp_consolidations')->cascadeOnDelete();
            $table->foreignUuid('ppmp_consolidation_item_id')->nullable()->constrained('ppmp_consolidation_items')->nullOnDelete();
            $table->string('program')->nullable();
            $table->string('activity')->nullable();
            $table->string('project')->nullable();
            $table->string('procurement_item');
            $table->decimal('quantity', 18, 2)->default(0);
            $table->string('unit')->nullable();
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->decimal('annual_requirement', 18, 2)->default(0);
            $table->decimal('budget_allocation', 18, 2)->default(0);
            $table->string('fund_source')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ppmp_consolidation_wfp_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ppmp_consolidation_id')->constrained('ppmp_consolidations')->cascadeOnDelete();
            $table->string('activity');
            $table->string('responsible_office')->nullable();
            $table->string('expected_output')->nullable();
            $table->string('funding_source')->nullable();
            $table->decimal('budget_allocation', 18, 2)->default(0);
            $table->decimal('q1_budget', 18, 2)->default(0);
            $table->decimal('q2_budget', 18, 2)->default(0);
            $table->decimal('q3_budget', 18, 2)->default(0);
            $table->decimal('q4_budget', 18, 2)->default(0);
            $table->date('schedule_start')->nullable();
            $table->date('schedule_end')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ppmp_consolidation_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ppmp_consolidation_id')->constrained('ppmp_consolidations')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('status_at_snapshot');
            $table->json('snapshot');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['ppmp_consolidation_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppmp_consolidation_versions');
        Schema::dropIfExists('ppmp_consolidation_wfp_lines');
        Schema::dropIfExists('ppmp_consolidation_bp2020_lines');
        Schema::dropIfExists('ppmp_consolidation_items');
        Schema::dropIfExists('ppmp_consolidation_sources');
        Schema::dropIfExists('ppmp_consolidations');
    }
};
