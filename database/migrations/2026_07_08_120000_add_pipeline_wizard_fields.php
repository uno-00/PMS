<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_proposals', function (Blueprint $table) {
            $table->string('pipeline_step')->default('project_proposal')->after('status');
        });

        Schema::table('market_scopings', function (Blueprint $table) {
            $table->uuid('project_proposal_id')->nullable()->after('id');
            $table->foreign('project_proposal_id')->references('id')->on('project_proposals')->nullOnDelete();
        });

        Schema::table('project_proposals', function (Blueprint $table) {
            $table->dropForeign(['market_scoping_id']);
        });

        Schema::table('project_proposals', function (Blueprint $table) {
            $table->uuid('market_scoping_id')->nullable()->change();
            $table->foreign('market_scoping_id')->references('id')->on('market_scopings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_proposals', function (Blueprint $table) {
            $table->dropForeign(['market_scoping_id']);
        });

        Schema::table('project_proposals', function (Blueprint $table) {
            $table->uuid('market_scoping_id')->nullable(false)->change();
            $table->foreign('market_scoping_id')->references('id')->on('market_scopings')->cascadeOnDelete();
            $table->dropColumn('pipeline_step');
        });

        Schema::table('market_scopings', function (Blueprint $table) {
            $table->dropForeign(['project_proposal_id']);
            $table->dropColumn('project_proposal_id');
        });
    }
};
