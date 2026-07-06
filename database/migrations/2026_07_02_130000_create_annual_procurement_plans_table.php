<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annual_procurement_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fiscal_year_id')->unique();
            $table->uuid('gaa_id')->nullable();
            $table->string('reference_no')->nullable();
            $table->decimal('total_budget', 18, 2)->default(0);
            $table->decimal('total_planned_amount', 18, 2)->default(0);
            $table->string('status')->default('draft');
            $table->uuid('consolidated_by')->nullable();
            $table->timestamp('consolidated_at')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->uuid('locked_by')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('fiscal_year_id')->references('id')->on('fiscal_years')->cascadeOnDelete();
            $table->foreign('gaa_id')->references('id')->on('general_appropriations_acts')->nullOnDelete();
            $table->foreign('consolidated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('locked_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_procurement_plans');
    }
};
