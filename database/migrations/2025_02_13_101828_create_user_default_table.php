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
        Schema::create('user_default', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('company_id'); // Company ID (required)
            $table->integer('pay_period_schedule_id')->nullable(); // Pay period schedule ID (nullable)
            $table->integer('policy_group_id')->nullable(); // Policy group ID (nullable)
            $table->string('employee_number', 250)->nullable(); // Employee number (nullable)
            $table->string('city', 250)->nullable(); // City (nullable)
            $table->string('province', 250)->nullable(); // Province (nullable)
            $table->string('country', 250)->nullable(); // Country (nullable)
            $table->string('work_email', 250)->nullable(); // Work email (nullable)
            $table->string('work_phone', 250)->nullable(); // Work phone (nullable)
            $table->string('work_phone_ext', 250)->nullable(); // Work phone extension (nullable)
            $table->integer('hire_date')->nullable(); // Hire date (nullable)
            $table->integer('title_id')->nullable(); // Title ID (nullable)
            $table->integer('default_branch_id')->nullable(); // Default branch ID (nullable)
            $table->integer('default_department_id')->nullable(); // Default department ID (nullable)
            $table->string('date_format', 250)->nullable(); // Date format (nullable)
            $table->string('time_format', 250)->nullable(); // Time format (nullable)
            $table->string('time_unit_format', 250)->nullable(); // Time unit format (nullable)
            $table->string('time_zone', 250)->nullable(); // Time zone (nullable)
            $table->integer('items_per_page')->nullable(); // Items per page (nullable)
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->integer('start_week_day')->nullable(); // Start week day (nullable)
            $table->string('language', 5)->nullable(); // Language (nullable)
            $table->integer('currency_id')->nullable(); // Currency ID (nullable)
            $table->integer('permission_control_id')->nullable(); // Permission control ID (nullable)
            $table->tinyInteger('enable_email_notification_exception')->default(0); // Enable email notification exception (default 0)
            $table->tinyInteger('enable_email_notification_message')->default(0); // Enable email notification message (default 0)
            $table->tinyInteger('enable_email_notification_home')->default(0); // Enable email notification home (default 0)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_default');
    }
};
