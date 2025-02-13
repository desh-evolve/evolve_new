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
        Schema::create('punch_control', function (Blueprint $table) {
            $table->id(); // auto-increment primary key
            $table->integer('user_date_id'); // Foreign key to `user_date`
            $table->integer('branch_id')->nullable(); // Foreign key to `branch`
            $table->integer('department_id')->nullable(); // Foreign key to `department`
            $table->integer('job_id')->nullable(); // Foreign key to `job`
            $table->integer('job_item_id')->nullable(); // Foreign key to `job_item`
            $table->decimal('quantity', 9, 2)->nullable(); // Quantity of work
            $table->decimal('bad_quantity', 9, 2)->nullable(); // Bad quantity of work
            $table->integer('total_time')->default(0); // Total time in minutes or seconds
            $table->integer('actual_total_time')->default(0); // Actual total time worked
            $table->integer('meal_policy_id')->nullable(); // Foreign key to `meal_policy`
            $table->boolean('overlap')->default(0); // Overlap flag
            $table->integer('created_date')->nullable(); // Created date (Unix timestamp)
            $table->integer('created_by')->nullable(); // Created by user ID
            $table->integer('updated_date')->nullable(); // Updated date
            $table->integer('updated_by')->nullable(); // Updated by user ID
            $table->integer('deleted_date')->nullable(); // Deleted date
            $table->integer('deleted_by')->nullable(); // Deleted by user ID
            $table->boolean('deleted')->default(0); // Deleted flag
            $table->string('other_id1', 255)->nullable(); // Other ID field 1
            $table->string('other_id2', 255)->nullable(); // Other ID field 2
            $table->string('other_id3', 255)->nullable(); // Other ID field 3
            $table->string('other_id4', 255)->nullable(); // Other ID field 4
            $table->string('other_id5', 255)->nullable(); // Other ID field 5
            $table->string('note', 1024)->nullable(); // Notes field
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('punch_control');
    }
};
