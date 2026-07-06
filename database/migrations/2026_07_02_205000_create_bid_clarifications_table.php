<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Phase 9 "Ask Clarification": a bidder posts a question against a
        // posted procurement and the BAC Secretariat/TWG answers it. Kept as
        // its own lightweight table (rather than overloading documents)
        // since it is plain structured Q&A, not a file.
        Schema::create('bid_clarifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('procurement_id');
            $table->uuid('bidder_id');
            $table->text('question');
            $table->text('answer')->nullable();
            $table->string('status')->default('pending'); // pending, answered
            $table->uuid('asked_by')->nullable();
            $table->uuid('answered_by')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->foreign('procurement_id')->references('id')->on('procurements')->cascadeOnDelete();
            $table->foreign('bidder_id')->references('id')->on('bidders')->cascadeOnDelete();
            $table->foreign('asked_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('answered_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bid_clarifications');
    }
};
