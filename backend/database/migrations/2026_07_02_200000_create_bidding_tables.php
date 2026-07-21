<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bid_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('bid_no')->unique();
            $table->uuid('procurement_id');
            $table->uuid('bidder_id');
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('submitted_at')->nullable();
            $table->boolean('is_late')->default(false);
            $table->string('status')->default('submitted'); // submitted, opened, disqualified, withdrawn
            $table->timestamps();

            $table->foreign('procurement_id')->references('id')->on('procurements')->cascadeOnDelete();
            $table->foreign('bidder_id')->references('id')->on('bidders')->cascadeOnDelete();
        });

        Schema::create('bid_openings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('procurement_id')->unique();
            $table->dateTime('opened_at');
            $table->uuid('opened_by')->nullable();
            $table->json('checklist')->nullable();
            $table->json('attendance')->nullable();
            $table->timestamps();

            $table->foreign('procurement_id')->references('id')->on('procurements')->cascadeOnDelete();
            $table->foreign('opened_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('bid_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bid_submission_id');
            $table->uuid('evaluator_id')->nullable();
            $table->json('compliance')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->unsignedInteger('rank')->nullable();
            $table->text('remarks')->nullable();
            $table->string('recommendation')->nullable(); // recommended, not_recommended
            $table->timestamps();

            $table->foreign('bid_submission_id')->references('id')->on('bid_submissions')->cascadeOnDelete();
            $table->foreign('evaluator_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('post_qualifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('procurement_id');
            $table->uuid('bidder_id');
            $table->boolean('site_visit_conducted')->default(false);
            $table->text('document_validation_notes')->nullable();
            $table->string('result')->nullable(); // passed, failed
            $table->uuid('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('procurement_id')->references('id')->on('procurements')->cascadeOnDelete();
            $table->foreign('bidder_id')->references('id')->on('bidders')->cascadeOnDelete();
            $table->foreign('processed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_qualifications');
        Schema::dropIfExists('bid_evaluations');
        Schema::dropIfExists('bid_openings');
        Schema::dropIfExists('bid_submissions');
    }
};
