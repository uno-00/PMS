<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('workflowable');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('action');
            $table->text('remarks')->nullable();
            $table->uuid('performed_by')->nullable();
            $table->string('performed_role')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('performed_at');

            $table->foreign('performed_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['workflowable_type', 'workflowable_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_histories');
    }
};
