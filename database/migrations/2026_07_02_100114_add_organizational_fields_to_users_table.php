<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('division_id')->nullable()->after('signature_path');
            $table->uuid('office_id')->nullable()->after('division_id');

            $table->foreign('division_id')->references('id')->on('divisions')->nullOnDelete();
            $table->foreign('office_id')->references('id')->on('offices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropForeign(['office_id']);
            $table->dropColumn(['division_id', 'office_id']);
        });
    }
};
