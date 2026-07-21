<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->string('name');
            $table->string('type')->default('regular'); // regular, special_non_working
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['date', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
