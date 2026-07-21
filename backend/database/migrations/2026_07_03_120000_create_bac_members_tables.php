<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bac_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('bac_role');
            $table->string('designation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('term_start')->nullable();
            $table->date('term_end')->nullable();
            $table->timestamps();

            $table->index(['bac_role', 'is_active']);
        });

        Schema::create('bac_twg_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bac_member_id')->constrained('bac_members')->cascadeOnDelete();
            $table->string('category');
            $table->timestamps();

            $table->unique(['bac_member_id', 'category']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bac_twg_assignments');
        Schema::dropIfExists('bac_members');
    }
};
