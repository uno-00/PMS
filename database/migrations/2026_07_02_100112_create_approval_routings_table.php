<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Configurable "who approves what stage" matrix so approval routing
        // can be re-assigned by a Super Admin without a code deployment.
        // The workflow *sequence* (Draft -> ... -> Approved) is enforced by
        // the status enums; this table only configures the responsible role
        // for each step key within that fixed sequence.
        Schema::create('approval_routings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('document_type'); // gaa, app, ppmp, purchase_request, caf, award, ntp, purchase_order
            $table->string('step_key'); // e.g. division_chief_review, planning_review, budget_validation
            $table->string('step_label');
            $table->unsignedInteger('sequence');
            $table->string('role_name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['document_type', 'step_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_routings');
    }
};
