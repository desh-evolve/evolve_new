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
        Schema::create('company_deduction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('type_id');
            $table->string('name', 250);
            $table->unsignedBigInteger('calculation_id');
            $table->unsignedInteger('calculation_order')->default(0);
            $table->string('country', 250)->nullable();
            $table->string('province', 250)->nullable();
            $table->string('district', 250)->nullable();
            $table->string('company_value1', 250)->nullable();
            $table->string('company_value2', 250)->nullable();
            $table->string('user_value1', 250)->nullable();
            $table->string('user_value2', 250)->nullable();
            $table->string('user_value3', 250)->nullable();
            $table->string('user_value4', 250)->nullable();
            $table->string('user_value5', 250)->nullable();
            $table->string('user_value6', 250)->nullable();
            $table->string('user_value7', 250)->nullable();
            $table->string('user_value8', 250)->nullable();
            $table->string('user_value9', 250)->nullable();
            $table->string('user_value10', 250)->nullable();
            $table->tinyInteger('lock_user_value1')->default(0);
            $table->tinyInteger('lock_user_value2')->default(0);
            $table->tinyInteger('lock_user_value3')->default(0);
            $table->tinyInteger('lock_user_value4')->default(0);
            $table->tinyInteger('lock_user_value5')->default(0);
            $table->tinyInteger('lock_user_value6')->default(0);
            $table->tinyInteger('lock_user_value7')->default(0);
            $table->tinyInteger('lock_user_value8')->default(0);
            $table->tinyInteger('lock_user_value9')->default(0);
            $table->tinyInteger('lock_user_value10')->default(0);
            $table->unsignedBigInteger('pay_stub_entry_account_id');
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->tinyInteger('deleted')->default(0);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->decimal('minimum_length_of_service', 11, 4)->nullable();
            $table->smallInteger('minimum_length_of_service_unit_id')->nullable();
            $table->decimal('minimum_length_of_service_days', 11, 4)->nullable();
            $table->decimal('maximum_length_of_service', 11, 4)->nullable();
            $table->smallInteger('maximum_length_of_service_unit_id')->nullable();
            $table->decimal('maximum_length_of_service_days', 11, 4)->nullable();
            $table->smallInteger('include_account_amount_type_id')->default(10);
            $table->smallInteger('exclude_account_amount_type_id')->default(10);
            $table->decimal('minimum_user_age', 11, 4)->nullable();
            $table->decimal('maximum_user_age', 11, 4)->nullable();
            $table->tinyInteger('basis_of_employment')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_deduction');
    }
};
