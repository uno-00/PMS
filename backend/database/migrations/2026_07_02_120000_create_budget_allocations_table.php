<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fiscal_year_id');
            $table->uuid('parent_id')->nullable();
            $table->uuid('gaa_id')->nullable();
            $table->string('level'); // department, division, office, cost_center
            $table->uuid('department_id')->nullable();
            $table->uuid('division_id')->nullable();
            $table->uuid('office_id')->nullable();
            $table->uuid('cost_center_id')->nullable();
            $table->uuid('pap_id')->nullable();
            $table->uuid('fund_source_id')->nullable();
            $table->uuid('uacs_code_id')->nullable();
            $table->decimal('allocated_amount', 18, 2)->default(0);
            $table->decimal('utilized_amount', 18, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('fiscal_year_id')->references('id')->on('fiscal_years')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('budget_allocations')->nullOnDelete();
            $table->foreign('gaa_id')->references('id')->on('general_appropriations_acts')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('division_id')->references('id')->on('divisions')->nullOnDelete();
            $table->foreign('office_id')->references('id')->on('offices')->nullOnDelete();
            $table->foreign('cost_center_id')->references('id')->on('cost_centers')->nullOnDelete();
            $table->foreign('pap_id')->references('id')->on('paps')->nullOnDelete();
            $table->foreign('fund_source_id')->references('id')->on('fund_sources')->nullOnDelete();
            $table->foreign('uacs_code_id')->references('id')->on('uacs_codes')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['fiscal_year_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_allocations');
    }
};
