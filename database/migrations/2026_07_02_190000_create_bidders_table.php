<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Phase 9: separate Bidder/Supplier Portal. A bidder may optionally
        // be linked to a `users` row (guard "web", role "Bidder") for portal
        // login; the business/eligibility profile lives here regardless.
        Schema::create('bidders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->unique();
            $table->string('company_name');
            $table->string('business_type')->nullable();
            $table->string('philgeps_registration_no')->nullable();
            $table->date('philgeps_registration_expiry')->nullable();
            $table->string('mayor_permit_no')->nullable();
            $table->date('mayor_permit_expiry')->nullable();
            $table->string('tax_clearance_no')->nullable();
            $table->date('tax_clearance_expiry')->nullable();
            $table->string('sec_dti_registration_no')->nullable();
            $table->string('pcab_license_no')->nullable();
            $table->date('pcab_license_expiry')->nullable();
            $table->string('tin', 20)->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('pending'); // pending, verified, suspended, rejected
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('bid_document_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_no')->unique();
            $table->uuid('procurement_id');
            $table->uuid('bidder_id');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('payment_status')->default('pending'); // pending, paid
            $table->string('or_no')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('procurement_id')->references('id')->on('procurements')->cascadeOnDelete();
            $table->foreign('bidder_id')->references('id')->on('bidders')->cascadeOnDelete();
            $table->unique(['procurement_id', 'bidder_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bid_document_orders');
        Schema::dropIfExists('bidders');
    }
};
