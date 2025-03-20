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
        Schema::create('pay_period_schedule', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->string('name', 250);
            $table->string('description', 250)->nullable();
            $table->integer('type_id');
            $table->boolean('primary_date_ldom')->nullable();
            $table->boolean('primary_transaction_date_ldom')->nullable();
            $table->boolean('primary_transaction_date_bd')->nullable();
            $table->boolean('secondary_date_ldom')->nullable();
            $table->boolean('secondary_transaction_date_ldom')->nullable();
            $table->boolean('secondary_transaction_date_bd')->nullable();
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->timestamp('anchor_date')->nullable();
            $table->timestamp('primary_date')->nullable();
            $table->timestamp('primary_transaction_date')->nullable();
            $table->timestamp('secondary_date')->nullable();
            $table->timestamp('secondary_transaction_date')->nullable();
            $table->integer('day_start_time')->nullable();
            $table->integer('day_continuous_time')->nullable();
            $table->integer('start_week_day_id')->nullable();
            $table->smallInteger('start_day_of_week')->nullable();
            $table->smallInteger('transaction_date')->nullable();
            $table->smallInteger('primary_day_of_month')->nullable();
            $table->smallInteger('secondary_day_of_month')->nullable();
            $table->smallInteger('primary_transaction_day_of_month')->nullable();
            $table->smallInteger('secondary_transaction_day_of_month')->nullable();
            $table->smallInteger('transaction_date_bd')->nullable();
            $table->string('time_zone', 250)->nullable();
            $table->integer('new_day_trigger_time')->nullable();
            $table->integer('maximum_shift_time')->nullable();
            $table->integer('shift_assigned_day_id')->nullable();
            $table->integer('timesheet_verify_before_end_date')->nullable();
            $table->integer('timesheet_verify_before_transaction_date')->nullable();
            $table->integer('timesheet_verify_notice_before_transaction_date')->nullable();
            $table->integer('timesheet_verify_notice_email')->nullable();
            $table->integer('annual_pay_periods')->nullable();
            $table->integer('timesheet_verify_type_id')->default(10);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_period_schedule');
    }
};
