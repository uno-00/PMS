<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppmp_items', function (Blueprint $table) {
            $table->string('expense_class', 32)->default('mooe')->after('item_no');
        });
    }

    public function down(): void
    {
        Schema::table('ppmp_items', function (Blueprint $table) {
            $table->dropColumn('expense_class');
        });
    }
};
