<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_appropriations_acts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fiscal_year_id')->unique();
            $table->string('reference_no')->nullable();
            $table->string('title');
            $table->string('file_disk')->default('s3');
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->string('status')->default('draft');
            $table->json('validation_errors')->nullable();
            $table->uuid('uploaded_by')->nullable();
            $table->uuid('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->uuid('distributed_by')->nullable();
            $table->timestamp('distributed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('fiscal_year_id')->references('id')->on('fiscal_years')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('validated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('distributed_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('gaa_line_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('gaa_id');
            $table->unsignedInteger('line_no')->default(0);
            $table->uuid('department_id')->nullable();
            $table->uuid('division_id')->nullable();
            $table->uuid('pap_id')->nullable();
            $table->uuid('uacs_code_id')->nullable();
            $table->uuid('fund_source_id')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount', 18, 2)->default(0);
            $table->timestamps();

            $table->foreign('gaa_id')->references('id')->on('general_appropriations_acts')->cascadeOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('division_id')->references('id')->on('divisions')->nullOnDelete();
            $table->foreign('pap_id')->references('id')->on('paps')->nullOnDelete();
            $table->foreign('uacs_code_id')->references('id')->on('uacs_codes')->nullOnDelete();
            $table->foreign('fund_source_id')->references('id')->on('fund_sources')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gaa_line_items');
        Schema::dropIfExists('general_appropriations_acts');
    }
};
