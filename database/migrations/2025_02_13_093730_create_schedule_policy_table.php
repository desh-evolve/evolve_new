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
        Schema::dropIfExists('schedule_policy');
    }
};
