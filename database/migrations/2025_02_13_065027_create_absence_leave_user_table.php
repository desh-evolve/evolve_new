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
        Schema::create('absence_leave_user', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->unsignedBigInteger('absence_leave_id');
            $table->unsignedBigInteger('absence_policy_id');
            $table->decimal('amount', 18, 4);
            $table->string('leave_date_year', 11);
            $table->unsignedSmallInteger('basis_employment');
            $table->unsignedSmallInteger('leave_applicable');
            $table->decimal('minimum_length_of_service', 11, 4);
            $table->unsignedSmallInteger('minimum_length_of_service_unit_id');
            $table->decimal('maximum_length_of_service', 11, 4);
            $table->unsignedSmallInteger('maximum_length_of_service_unit_id');
            $table->unsignedSmallInteger('deleted');
            $table->unsignedSmallInteger('status');
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absence_leave_user');
    }
};
