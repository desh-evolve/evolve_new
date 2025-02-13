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
        Schema::create('schedule', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('user_date_id'); // Foreign key to `user_date` table
            $table->integer('status_id'); // Foreign key to `status` table
            $table->timestamp('start_time')->nullable(); // Start time (nullable)
            $table->timestamp('end_time')->nullable(); // End time (nullable)
            $table->integer('schedule_policy_id')->nullable(); // Foreign key to `schedule_policy` table
            $table->integer('absence_policy_id')->nullable(); // Foreign key to `absence_policy` table
            $table->integer('branch_id')->nullable(); // Foreign key to `branch` table
            $table->integer('department_id')->nullable(); // Foreign key to `department` table
            $table->integer('job_id')->nullable(); // Foreign key to `job` table
            $table->integer('job_item_id')->nullable(); // Foreign key to `job_item` table
            $table->integer('created_date')->nullable(); // Created timestamp (nullable)
            $table->integer('created_by')->nullable(); // Created by user ID (nullable)
            $table->integer('updated_date')->nullable(); // Updated timestamp (nullable)
            $table->integer('updated_by')->nullable(); // Updated by user ID (nullable)
            $table->integer('deleted_date')->nullable(); // Deleted timestamp (nullable)
            $table->integer('deleted_by')->nullable(); // Deleted by user ID (nullable)
            $table->tinyInteger('deleted')->default(0); // Deleted flag (0 = not deleted)
            $table->integer('total_time')->nullable(); // Total time (nullable)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule');
    }
};
