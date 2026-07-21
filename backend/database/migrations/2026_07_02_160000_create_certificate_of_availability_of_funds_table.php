<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_of_availability_of_funds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('caf_no')->unique();
            $table->uuid('purchase_request_id')->unique();
            $table->uuid('fund_source_id')->nullable();
            $table->uuid('uacs_code_id')->nullable();
            $table->decimal('amount', 18, 2)->default(0);
            $table->decimal('remaining_budget', 18, 2)->default(0);
            $table->string('status')->default('generated');
            $table->uuid('verification_code')->unique();
            $table->uuid('generated_by')->nullable();
            $table->uuid('certified_by')->nullable();
            $table->timestamp('certified_at')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();

            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->cascadeOnDelete();
            $table->foreign('fund_source_id')->references('id')->on('fund_sources')->nullOnDelete();
            $table->foreign('uacs_code_id')->references('id')->on('uacs_codes')->nullOnDelete();
            $table->foreign('generated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('certified_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_of_availability_of_funds');
    }
};
