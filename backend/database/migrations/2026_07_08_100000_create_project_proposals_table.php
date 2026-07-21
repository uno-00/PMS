<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_proposals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('control_no')->nullable()->unique();
            $table->string('document_ref')->default('NMP-PP-01');
            $table->string('with_enclosures')->nullable();
            $table->uuid('market_scoping_id');
            $table->uuid('fiscal_year_id')->nullable();
            $table->uuid('division_id');
            $table->string('project_type')->nullable();
            $table->string('title');
            $table->string('schedule')->nullable();
            $table->string('venue_area')->nullable();
            $table->decimal('total_cost', 18, 2)->default(0);
            $table->string('fund_source_text')->nullable();
            $table->string('proponent')->nullable();
            $table->text('rationale')->nullable();
            $table->text('objectives')->nullable();
            $table->text('target_schedule')->nullable();
            $table->text('budgetary_requirement')->nullable();
            $table->text('fund_source_narrative')->nullable();
            $table->string('status')->default('draft');
            $table->uuid('ppmp_id')->nullable();
            $table->uuid('prepared_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->uuid('recommended_by')->nullable();
            $table->timestamp('recommended_at')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('market_scoping_id')->references('id')->on('market_scopings')->cascadeOnDelete();
            $table->foreign('fiscal_year_id')->references('id')->on('fiscal_years')->nullOnDelete();
            $table->foreign('division_id')->references('id')->on('divisions')->cascadeOnDelete();
            $table->foreign('ppmp_id')->references('id')->on('ppmps')->nullOnDelete();
            $table->foreign('prepared_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('recommended_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_proposals');
    }
};
