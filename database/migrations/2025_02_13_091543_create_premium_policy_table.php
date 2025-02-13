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
        Schema::create('premium_policy', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id'); // Foreign key to `company` table
            $table->string('name', 250);
            $table->integer('type_id');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            
            // Days of the week
            $table->tinyInteger('sun')->default(0);
            $table->tinyInteger('mon')->default(0);
            $table->tinyInteger('tue')->default(0);
            $table->tinyInteger('wed')->default(0);
            $table->tinyInteger('thu')->default(0);
            $table->tinyInteger('fri')->default(0);
            $table->tinyInteger('sat')->default(0);

            $table->integer('pay_type_id');
            $table->decimal('rate', 9, 4)->nullable();
            $table->integer('accrual_policy_id')->nullable();
            $table->decimal('accrual_rate', 9, 4)->nullable();
            $table->integer('pay_stub_entry_account_id')->nullable();

            // Audit fields
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->tinyInteger('deleted')->default(0);

            // Time-related fields
            $table->integer('daily_trigger_time')->nullable();
            $table->integer('weekly_trigger_time')->nullable();
            $table->integer('minimum_time')->nullable();
            $table->integer('maximum_time')->nullable();

            // Additional settings and policy fields
            $table->smallInteger('include_meal_policy')->nullable();
            $table->smallInteger('exclude_default_branch')->nullable();
            $table->smallInteger('exclude_default_department')->nullable();
            $table->smallInteger('branch_selection_type_id')->nullable();
            $table->smallInteger('department_selection_type_id')->nullable();
            $table->smallInteger('job_selection_type_id')->nullable();
            $table->smallInteger('job_group_selection_type_id')->nullable();
            $table->smallInteger('job_item_selection_type_id')->nullable();
            $table->smallInteger('job_item_group_selection_type_id')->nullable();
            $table->integer('maximum_no_break_time')->nullable();
            $table->integer('minimum_break_time')->nullable();
            $table->tinyInteger('include_partial_punch')->default(0);
            $table->integer('wage_group_id')->default(0);
            $table->smallInteger('include_break_policy')->default(0);
            $table->integer('minimum_time_between_shift')->nullable();
            $table->integer('minimum_first_shift_time')->nullable();
            $table->integer('minimum_shift_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium_policy');
    }
};
