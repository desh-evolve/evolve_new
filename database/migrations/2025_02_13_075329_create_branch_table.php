<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('branch', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(0);
            $table->unsignedBigInteger('status_id');
            $table->string('name', 250)->nullable();
            $table->string('address1', 250)->nullable();
            $table->string('address2', 250)->nullable();
            $table->string('city', 250)->nullable();
            $table->string('province', 250)->nullable();
            $table->string('country', 250)->nullable();
            $table->string('postal_code', 250)->nullable();
            $table->string('work_phone', 250)->nullable();
            $table->string('fax_phone', 250)->nullable();
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->integer('manual_id')->nullable();
            $table->string('name_metaphone', 250)->nullable();
            $table->decimal('longitude', 15, 10)->nullable();
            $table->decimal('latitude', 15, 10)->nullable();
            $table->string('branch_short_id', 250)->nullable();
            $table->string('epf_no', 250)->nullable();
            $table->string('etf_no', 250)->nullable();
            $table->string('tin_no', 250)->nullable();
            $table->string('business_reg_no', 250)->nullable();
            $table->string('other_id1', 250)->nullable();
            $table->string('other_id2', 250)->nullable();
            $table->string('other_id3', 250)->nullable();
            $table->string('other_id4', 250)->nullable();
            $table->string('other_id5', 250)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch');
    }
};
