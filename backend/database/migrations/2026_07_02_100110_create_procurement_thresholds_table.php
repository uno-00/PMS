<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_thresholds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mode_of_procurement_id');
            $table->string('category'); // goods, infrastructure, consulting_services
            $table->decimal('min_amount', 18, 2)->default(0);
            $table->decimal('max_amount', 18, 2)->nullable();
            $table->date('effective_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('mode_of_procurement_id')->references('id')->on('modes_of_procurement')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_thresholds');
    }
};
