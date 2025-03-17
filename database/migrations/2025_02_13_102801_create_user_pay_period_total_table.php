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
        Schema::create('user_pay_period_total', function (Blueprint $table) {
            $table->id(); // Auto-incrementing unsigned bigint ID
            $table->integer('pay_period_id'); // Pay period ID
            $table->integer('user_id'); // User ID
            $table->integer('schedule_total_time')->nullable(); // Scheduled total time
            $table->integer('schedule_bank_time')->nullable(); // Scheduled bank time
            $table->integer('schedule_sick_time')->nullable(); // Scheduled sick time
            $table->integer('schedule_vacation_time')->nullable(); // Scheduled vacation time
            $table->integer('schedule_statutory_time')->nullable(); // Scheduled statutory time
            $table->integer('schedule_over_time_1')->nullable(); // Scheduled over time 1
            $table->integer('schedule_over_time_2')->nullable(); // Scheduled over time 2
            $table->integer('actual_total_time')->nullable(); // Actual total time
            $table->integer('total_time')->nullable(); // Total time
            $table->integer('bank_time')->nullable(); // Bank time
            $table->integer('sick_time')->nullable(); // Sick time
            $table->integer('vacation_time')->nullable(); // Vacation time
            $table->integer('statutory_time')->nullable(); // Statutory time
            $table->integer('over_time_1')->nullable(); // Over time 1
            $table->integer('over_time_2')->nullable(); // Over time 2
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->integer('schedule_bank_time_2')->nullable(); // Additional scheduled bank time 2
            $table->integer('schedule_bank_time_3')->nullable(); // Additional scheduled bank time 3
            $table->integer('bank_time_2')->nullable(); // Additional bank time 2
            $table->integer('bank_time_3')->nullable(); // Additional bank time 3
            $table->integer('schedule_regular_time')->nullable(); // Scheduled regular time
            $table->integer('schedule_payable_time')->nullable(); // Scheduled payable time
            $table->integer('regular_time')->nullable(); // Regular time
            $table->integer('payable_time')->nullable(); // Payable time
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_pay_period_total');
    }
};
