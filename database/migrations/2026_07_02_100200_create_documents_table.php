<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Generic, polymorphic document ledger backing every module's file
        // uploads (PPMP, APP, PR, CAF, BAC minutes, PhilGEPS postings, bid
        // documents, awards, NTP, purchase orders, reports). Provides
        // application-level version history on top of S3 bucket versioning.
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('documentable');
            $table->string('module'); // maps to the top-level S3 folder, e.g. ppmp, app, pr, caf, bac, philgeps, bids, award, ntp, purchase-order, reports
            $table->string('category')->default('attachment'); // source-file, minutes, attendance-sheet, signed-copy, ...
            $table->string('disk');
            $table->string('path');
            $table->string('original_filename');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->string('checksum', 64)->nullable();
            $table->uuid('uploaded_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('retain_until')->nullable();
            $table->timestamps();

            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['documentable_type', 'documentable_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
