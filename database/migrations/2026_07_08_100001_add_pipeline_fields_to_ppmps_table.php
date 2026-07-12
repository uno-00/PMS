<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppmps', function (Blueprint $table) {
            $table->uuid('project_proposal_id')->nullable()->after('parent_id');
            $table->uuid('market_scoping_id')->nullable()->after('project_proposal_id');
            $table->uuid('procurement_mode_by')->nullable()->after('bac_at');
            $table->timestamp('procurement_mode_at')->nullable()->after('procurement_mode_by');

            $table->foreign('project_proposal_id')->references('id')->on('project_proposals')->nullOnDelete();
            $table->foreign('market_scoping_id')->references('id')->on('market_scopings')->nullOnDelete();
            $table->foreign('procurement_mode_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ppmps', function (Blueprint $table) {
            $table->dropForeign(['project_proposal_id']);
            $table->dropForeign(['market_scoping_id']);
            $table->dropForeign(['procurement_mode_by']);
            $table->dropColumn([
                'project_proposal_id',
                'market_scoping_id',
                'procurement_mode_by',
                'procurement_mode_at',
            ]);
        });
    }
};
