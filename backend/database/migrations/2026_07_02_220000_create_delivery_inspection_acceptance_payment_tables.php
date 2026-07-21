<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('purchase_order_id');
            $table->string('delivery_receipt_no')->nullable();
            $table->date('delivery_date');
            $table->uuid('received_by')->nullable();
            $table->string('status')->default('delivered'); // delivered, partial, rejected
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            $table->foreign('received_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('delivery_id');
            $table->uuid('inspected_by')->nullable();
            $table->date('inspection_date');
            $table->string('result')->default('passed'); // passed, failed
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('delivery_id')->references('id')->on('deliveries')->cascadeOnDelete();
            $table->foreign('inspected_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('acceptances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('delivery_id');
            $table->uuid('accepted_by')->nullable();
            $table->date('accepted_date');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('delivery_id')->references('id')->on('deliveries')->cascadeOnDelete();
            $table->foreign('accepted_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('purchase_order_id');
            $table->string('or_no')->nullable();
            $table->decimal('amount', 18, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->string('method')->nullable(); // check, bank_transfer, ada
            $table->string('status')->default('pending'); // pending, processed, released
            $table->uuid('processed_by')->nullable();
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            $table->foreign('processed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('acceptances');
        Schema::dropIfExists('inspections');
        Schema::dropIfExists('deliveries');
    }
};
