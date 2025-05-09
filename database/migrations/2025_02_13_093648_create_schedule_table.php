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
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
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
