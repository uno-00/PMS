<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppmp_items', function (Blueprint $table) {
            $table->string('pre_procurement_conference')->nullable()->after('mode_of_procurement_id');
        });
    }

    public function down(): void
    {
        Schema::table('ppmp_items', function (Blueprint $table) {
            $table->dropColumn('pre_procurement_conference');
        });
    }
};
