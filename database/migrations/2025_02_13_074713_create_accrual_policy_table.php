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
        Schema::create('accrual_policy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name', 250);
            $table->unsignedBigInteger('type_id');
            $table->integer('minimum_time')->nullable();
            $table->integer('maximum_time')->nullable();
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->smallInteger('apply_frequency_id')->nullable();
            $table->smallInteger('apply_frequency_month')->nullable();
            $table->smallInteger('apply_frequency_day_of_month')->nullable();
            $table->smallInteger('apply_frequency_day_of_week')->nullable();
            $table->smallInteger('milestone_rollover_hire_date')->nullable();
            $table->smallInteger('milestone_rollover_month')->nullable();
            $table->smallInteger('milestone_rollover_day_of_month')->nullable();
            $table->integer('minimum_employed_days')->nullable();
            $table->smallInteger('minimum_employed_days_catchup')->nullable();
            $table->boolean('enable_pay_stub_balance_display')->default(false);
            $table->boolean('apply_frequency_hire_date')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accrual_policy');
    }
};
