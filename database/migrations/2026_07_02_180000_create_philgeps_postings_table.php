<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('philgeps_postings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('procurement_id')->unique();
            $table->string('reference_no')->nullable();
            $table->date('posting_date');
            $table->date('closing_date');
            $table->string('status')->default('published'); // published, closed, cancelled
            $table->boolean('is_manual')->default(false);
            $table->text('remarks')->nullable();
            $table->uuid('posted_by')->nullable();
            $table->timestamps();

            $table->foreign('procurement_id')->references('id')->on('procurements')->cascadeOnDelete();
            $table->foreign('posted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('philgeps_postings');
    }
};
