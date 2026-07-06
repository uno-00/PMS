<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('divisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('department_id')->nullable();
            $table->string('code')->unique();
            $table->string('name');
            $table->uuid('chief_user_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('chief_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('offices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('division_id')->nullable();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('division_id')->references('id')->on('divisions')->nullOnDelete();
        });

        Schema::create('cost_centers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('division_id')->nullable();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('division_id')->references('id')->on('divisions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_centers');
        Schema::dropIfExists('offices');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('departments');
    }
};
