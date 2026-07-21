<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppmps', function (Blueprint $table) {
            $table->string('document_type')->default('indicative')->after('ppmp_type');
        });
    }

    public function down(): void
    {
        Schema::table('ppmps', function (Blueprint $table) {
            $table->dropColumn('document_type');
        });
    }
};
