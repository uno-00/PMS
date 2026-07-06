<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('acronym')->nullable();
            $table->string('agency_code')->nullable();
            $table->text('address')->nullable();
            $table->string('region')->nullable();
            $table->string('tin', 20)->nullable();
            $table->string('head_of_agency')->nullable();
            $table->string('hope_position')->nullable();
            $table->string('bac_chairperson')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('website')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('philgeps_organization_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_profiles');
    }
};
