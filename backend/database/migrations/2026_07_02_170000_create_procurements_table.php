<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The BAC "case file": one row per procurement undertaking, tying
        // together the source Purchase Request with PhilGEPS posting,
        // bidding, evaluation, post-qualification, award, NTP, and PO
        // (Phases 7-17). This is the aggregate root the BAC dashboard and
        // procurement calendar are built around.
        Schema::create('procurements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('case_no')->unique();
            $table->uuid('purchase_request_id')->unique();
            $table->uuid('mode_of_procurement_id')->nullable();
            $table->string('title');
            $table->decimal('abc', 18, 2)->default(0);
            $table->string('status')->default('planning');
            $table->uuid('bac_chairperson_id')->nullable();
            $table->uuid('created_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->cascadeOnDelete();
            $table->foreign('mode_of_procurement_id')->references('id')->on('modes_of_procurement')->nullOnDelete();
            $table->foreign('bac_chairperson_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('bac_calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('procurement_id')->nullable();
            $table->string('activity_type'); // pre_procurement_conference, pre_bid_conference, bid_opening, post_qualification, notice_of_award, notice_to_proceed, contract_signing, purchase_order
            $table->string('title');
            $table->dateTime('scheduled_at');
            $table->string('venue')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, completed, cancelled
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('procurement_id')->references('id')->on('procurements')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bac_calendar_events');
        Schema::dropIfExists('procurements');
    }
};
