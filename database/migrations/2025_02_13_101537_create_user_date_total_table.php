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
        Schema::create('user_date_total', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('user_date_id'); // User date ID (required)
            $table->integer('status_id'); // Status ID (required)
            $table->integer('type_id'); // Type ID (required)
            $table->integer('punch_control_id')->nullable(); // Punch control ID (nullable)
            $table->integer('over_time_policy_id')->nullable(); // Overtime policy ID (nullable)
            $table->integer('absence_policy_id')->nullable(); // Absence policy ID (nullable)
            $table->integer('premium_policy_id')->nullable(); // Premium policy ID (nullable)
            $table->integer('branch_id')->nullable(); // Branch ID (nullable)
            $table->integer('department_id')->nullable(); // Department ID (nullable)
            $table->integer('job_id')->nullable(); // Job ID (nullable)
            $table->integer('job_item_id')->nullable(); // Job item ID (nullable)
            $table->decimal('quantity', 9, 2)->nullable(); // Quantity (nullable)
            $table->decimal('bad_quantity', 9, 2)->nullable(); // Bad quantity (nullable)
            $table->timestamp('start_time_stamp')->nullable(); // Start timestamp (nullable)
            $table->timestamp('end_time_stamp')->nullable(); // End timestamp (nullable)
            $table->integer('total_time')->default(0); // Total time (default 0)
            $table->tinyInteger('override')->default(0); // Override (default 0)
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->integer('actual_total_time')->default(0); // Actual total time (default 0)
            $table->integer('meal_policy_id')->nullable(); // Meal policy ID (nullable)
            $table->integer('break_policy_id')->default(0); // Break policy ID (default 0)
            $table->string('comment_ot', 250); // Comment for overtime (required)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_date_total');
    }
};
