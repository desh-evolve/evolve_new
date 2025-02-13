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
        Schema::create('schedule_policy', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('company_id'); // Foreign key to `company` table
            $table->string('name', 250); // Name of the schedule policy
            $table->integer('meal_policy_id')->nullable(); // Foreign key to `meal_policy` table
            $table->integer('over_time_policy_id')->nullable(); // Foreign key to `over_time_policy` table
            $table->integer('absence_policy_id')->nullable(); // Foreign key to `absence_policy` table
            $table->integer('start_window'); // Start window
            $table->integer('start_stop_window')->nullable(); // Start stop window (nullable)
            $table->integer('created_date')->nullable(); // Created timestamp (nullable)
            $table->integer('created_by')->nullable(); // Created by user ID (nullable)
            $table->integer('updated_date')->nullable(); // Updated timestamp (nullable)
            $table->integer('updated_by')->nullable(); // Updated by user ID (nullable)
            $table->integer('deleted_date')->nullable(); // Deleted timestamp (nullable)
            $table->integer('deleted_by')->nullable(); // Deleted by user ID (nullable)
            $table->tinyInteger('deleted')->default(0); // Deleted flag (0 = not deleted)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_policy');
    }
};
