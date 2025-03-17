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
        Schema::create('recurring_schedule_template', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('recurring_schedule_template_control_id'); // Foreign key to `recurring_schedule_template_control`
            $table->integer('week'); // Week number
            $table->tinyInteger('sun')->default(0); // Sunday flag, default to 0
            $table->tinyInteger('mon')->default(0); // Monday flag, default to 0
            $table->tinyInteger('tue')->default(0); // Tuesday flag, default to 0
            $table->tinyInteger('wed')->default(0); // Wednesday flag, default to 0
            $table->tinyInteger('thu')->default(0); // Thursday flag, default to 0
            $table->tinyInteger('fri')->default(0); // Friday flag, default to 0
            $table->tinyInteger('sat')->default(0); // Saturday flag, default to 0
            $table->timestamp('start_time')->nullable(); // Start time, nullable
            $table->timestamp('end_time')->nullable(); // End time, nullable
            $table->integer('schedule_policy_id')->nullable(); // Foreign key to `schedule_policy`
            $table->integer('branch_id')->nullable(); // Foreign key to `branch`
            $table->integer('department_id')->nullable(); // Foreign key to `department`
            $table->integer('job_id')->nullable(); // Foreign key to `job`
            $table->integer('job_item_id')->nullable(); // Foreign key to `job_item`
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_schedule_template');
    }
};
