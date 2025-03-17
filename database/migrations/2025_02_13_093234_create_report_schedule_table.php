<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_schedule', function (Blueprint $table) {
            $table->id();
            $table->integer('user_report_data_id');
            $table->integer('state_id')->default(10);
            $table->integer('status_id')->default(10);
            $table->integer('priority_id');
            $table->string('name', 250)->nullable();
            $table->string('description', 250)->nullable();
            $table->timestamp('last_run_date')->useCurrent()->useCurrentOnUpdate();
            $table->integer('last_run_processing_time')->nullable();
            $table->integer('average_processing_time')->nullable();
            $table->bigInteger('total_processing_time')->nullable();
            $table->integer('total_runs')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('custom_frequency_id', 250)->nullable();
            $table->string('custom_frequency_data', 250)->nullable();
            $table->string('minute', 250)->nullable();
            $table->string('hour', 250)->nullable();
            $table->string('day_of_month', 250)->nullable();
            $table->string('month', 250)->nullable();
            $table->string('day_of_week', 250)->nullable();
            $table->smallInteger('home_email_cc')->default(0);
            $table->string('other_email', 250)->nullable();
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_schedule');
    }
};