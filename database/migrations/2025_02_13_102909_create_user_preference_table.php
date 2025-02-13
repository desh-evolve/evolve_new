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
        Schema::create('user_preference', function (Blueprint $table) {
            $table->id(); // Auto-incrementing unsigned bigint ID
            $table->integer('user_id'); // User ID
            $table->string('date_format', 250); // Date format
            $table->string('time_format', 250); // Time format
            $table->string('time_unit_format', 250); // Time unit format
            $table->string('time_zone', 250); // Time zone
            $table->integer('items_per_page')->nullable(); // Items per page
            $table->integer('timesheet_view')->nullable(); // Timesheet view
            $table->integer('start_week_day')->nullable(); // Start week day
            $table->integer('created_date')->nullable(); // Created date
            $table->integer('created_by')->nullable(); // Created by
            $table->integer('updated_date')->nullable(); // Updated date
            $table->integer('updated_by')->nullable(); // Updated by
            $table->integer('deleted_date')->nullable(); // Deleted date
            $table->integer('deleted_by')->nullable(); // Deleted by
            $table->tinyInteger('deleted')->default(0); // Deleted flag
            $table->string('language', 5)->nullable(); // Language
            $table->tinyInteger('enable_email_notification_exception')->default(0); // Email notification exception flag
            $table->tinyInteger('enable_email_notification_message')->default(0); // Email notification message flag
            $table->tinyInteger('enable_email_notification_home')->default(0); // Email notification home flag
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preference');
    }
};
