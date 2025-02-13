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
        Schema::create('station', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('company_id'); // Foreign key to the `company` table
            $table->integer('status_id'); // Foreign key to the `status` table
            $table->integer('type_id'); // Foreign key to the `type` table
            $table->string('station_id', 250); // Station ID
            $table->string('source', 250)->nullable(); // Source (nullable)
            $table->string('description', 250); // Description
            $table->integer('created_date')->nullable(); // Created timestamp (nullable)
            $table->integer('created_by')->nullable(); // Created by user ID (nullable)
            $table->integer('updated_date')->nullable(); // Updated timestamp (nullable)
            $table->integer('updated_by')->nullable(); // Updated by user ID (nullable)
            $table->integer('deleted_date')->nullable(); // Deleted timestamp (nullable)
            $table->integer('deleted_by')->nullable(); // Deleted by user ID (nullable)
            $table->tinyInteger('deleted')->default(0); // Deleted flag (0 = not deleted)
            $table->integer('allowed_date')->nullable(); // Allowed date (nullable)
            $table->integer('branch_id')->nullable(); // Foreign key to `branch` table (nullable)
            $table->integer('department_id')->nullable(); // Foreign key to `department` table (nullable)
            $table->string('time_zone', 250)->nullable(); // Time zone (nullable)
            $table->smallInteger('user_group_selection_type_id')->nullable(); // User group selection type ID (nullable)
            $table->smallInteger('branch_selection_type_id')->nullable(); // Branch selection type ID (nullable)
            $table->smallInteger('department_selection_type_id')->nullable(); // Department selection type ID (nullable)
            $table->integer('port')->nullable(); // Port (nullable)
            $table->string('user_name', 250)->nullable(); // User name (nullable)
            $table->string('password', 250)->nullable(); // Password (nullable)
            $table->integer('poll_frequency')->nullable(); // Poll frequency (nullable)
            $table->integer('push_frequency')->nullable(); // Push frequency (nullable)
            $table->timestamp('last_punch_time_stamp')->nullable(); // Last punch timestamp (nullable)
            $table->integer('last_poll_date')->nullable(); // Last poll date (nullable)
            $table->string('last_poll_status_message', 250)->nullable(); // Last poll status message (nullable)
            $table->integer('last_push_date')->nullable(); // Last push date (nullable)
            $table->string('last_push_status_message', 250)->nullable(); // Last push status message (nullable)
            $table->string('user_value_1', 250)->nullable(); // User value 1 (nullable)
            $table->string('user_value_2', 250)->nullable(); // User value 2 (nullable)
            $table->string('user_value_3', 250)->nullable(); // User value 3 (nullable)
            $table->string('user_value_4', 250)->nullable(); // User value 4 (nullable)
            $table->string('user_value_5', 250)->nullable(); // User value 5 (nullable)
            $table->integer('partial_push_frequency')->nullable(); // Partial push frequency (nullable)
            $table->integer('last_partial_push_date')->nullable(); // Last partial push date (nullable)
            $table->string('last_partial_push_status_message', 250)->nullable(); // Last partial push status message (nullable)
            $table->timestamp('pull_start_time')->nullable(); // Pull start time (nullable)
            $table->timestamp('pull_end_time')->nullable(); // Pull end time (nullable)
            $table->timestamp('push_start_time')->nullable(); // Push start time (nullable)
            $table->timestamp('push_end_time')->nullable(); // Push end time (nullable)
            $table->timestamp('partial_push_start_time')->nullable(); // Partial push start time (nullable)
            $table->timestamp('partial_push_end_time')->nullable(); // Partial push end time (nullable)
            $table->tinyInteger('enable_auto_punch_status')->default(0); // Enable auto punch status flag (default 0)
            $table->bigInteger('mode_flag')->default(1); // Mode flag (default 1)
            $table->string('work_code_definition', 250)->nullable(); // Work code definition (nullable)
            $table->integer('job_id')->default(0); // Foreign key to `job` table (default 0)
            $table->integer('job_item_id')->default(0); // Foreign key to `job_item` table (default 0)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('station');
    }
};
