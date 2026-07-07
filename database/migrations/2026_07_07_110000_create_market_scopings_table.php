<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_scopings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('control_no')->unique();
            $table->foreignUuid('fiscal_year_id')->nullable()->constrained('fiscal_years')->nullOnDelete();
            $table->foreignUuid('division_id')->constrained('divisions');
            $table->string('procuring_entity')->nullable();
            $table->string('end_user_unit')->nullable();
            $table->string('representative_name')->nullable();
            $table->string('representative_designation')->nullable();
            $table->string('project_name');
            $table->decimal('estimated_budget', 18, 2)->default(0);
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->date('expected_delivery')->nullable();
            $table->json('activities')->nullable();
            $table->json('parameters')->nullable();
            $table->string('status')->default('draft');
            $table->foreignUuid('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_scopings');
    }
};
