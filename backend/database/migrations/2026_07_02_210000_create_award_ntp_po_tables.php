<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notice_of_awards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('noa_no')->unique();
            $table->uuid('procurement_id')->unique();
            $table->uuid('bidder_id');
            $table->decimal('amount', 18, 2)->default(0);
            $table->dateTime('issued_at')->nullable();
            $table->string('status')->default('awarded'); // awarded, accepted, declined
            $table->uuid('approved_by')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('procurement_id')->references('id')->on('procurements')->cascadeOnDelete();
            $table->foreign('bidder_id')->references('id')->on('bidders')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('notice_to_proceeds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ntp_no')->unique();
            $table->uuid('procurement_id')->unique();
            $table->date('effectivity_date');
            $table->unsignedInteger('contract_duration_days')->nullable();
            $table->date('completion_date')->nullable();
            $table->dateTime('issued_at')->nullable();
            $table->string('status')->default('issued');
            $table->uuid('issued_by')->nullable();
            $table->timestamps();

            $table->foreign('procurement_id')->references('id')->on('procurements')->cascadeOnDelete();
            $table->foreign('issued_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('po_no')->unique();
            $table->uuid('procurement_id')->nullable();
            $table->uuid('purchase_request_id')->nullable();
            $table->uuid('bidder_id')->nullable();
            $table->uuid('mode_of_procurement_id')->nullable();
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->date('delivery_date')->nullable();
            $table->string('delivery_place')->nullable();
            $table->string('status')->default('draft'); // draft, approved, delivered, inspected, accepted, invoiced, paid, cancelled
            $table->uuid('prepared_by')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('procurement_id')->references('id')->on('procurements')->nullOnDelete();
            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->nullOnDelete();
            $table->foreign('bidder_id')->references('id')->on('bidders')->nullOnDelete();
            $table->foreign('mode_of_procurement_id')->references('id')->on('modes_of_procurement')->nullOnDelete();
            $table->foreign('prepared_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('purchase_order_id');
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('quantity', 14, 2)->default(0);
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->decimal('amount', 18, 2)->default(0);
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('notice_to_proceeds');
        Schema::dropIfExists('notice_of_awards');
    }
};
