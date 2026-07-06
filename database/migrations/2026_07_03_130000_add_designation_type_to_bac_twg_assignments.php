<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bac_twg_assignments', function (Blueprint $table) {
            $table->string('designation_type')->default('primary')->after('category');
        });

        Schema::table('bac_twg_assignments', function (Blueprint $table) {
            $table->unique(['category', 'designation_type']);
        });
    }

    public function down(): void
    {
        Schema::table('bac_twg_assignments', function (Blueprint $table) {
            $table->dropUnique(['category', 'designation_type']);
            $table->dropColumn('designation_type');
        });
    }
};
