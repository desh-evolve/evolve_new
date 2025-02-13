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
        Schema::create('holiday_policy', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->integer('name');
            $table->integer('type_id');
            $table->integer('default_schedule_status_id');
            $table->integer('minimum_employed_days');
            $table->integer('minimum_worked_period_days')->nullable();
            $table->integer('minimum_worked_days')->nullable();
            $table->integer('average_time_days')->nullable();
            $table->tinyInteger('include_over_time')->default(0);
            $table->tinyInteger('include_paid_absence_time')->default(0);
            $table->integer('minimum_time')->nullable();
            $table->integer('maximum_time')->nullable();
            $table->integer('time')->nullable();
            $table->integer('absence_policy_id')->nullable();
            $table->integer('round_interval_policy_id')->nullable();
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->tinyInteger('deleted')->default(0);
            $table->tinyInteger('force_over_time_policy')->nullable()->default(0);
            $table->tinyInteger('average_time_worked_days')->nullable()->default(1);
            $table->smallInteger('worked_scheduled_days')->nullable()->default(0);
            $table->integer('minimum_worked_after_period_days')->nullable()->default(0);
            $table->integer('minimum_worked_after_days')->nullable()->default(0);
            $table->smallInteger('worked_after_scheduled_days')->nullable()->default(0);
            $table->smallInteger('paid_absence_as_worked')->nullable()->default(0);
            $table->integer('average_days')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_policy');
    }
};
