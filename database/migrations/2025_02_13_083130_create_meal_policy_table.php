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
        Schema::create('meal_policy', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->string('name', 250);
            $table->integer('type_id');
            $table->integer('amount');
            $table->integer('trigger_time')->nullable();
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->tinyInteger('deleted')->default(0);
            $table->integer('start_window')->nullable();
            $table->integer('window_length')->nullable();
            $table->smallInteger('include_lunch_punch_time')->nullable();
            $table->integer('auto_detect_type_id')->default(10);
            $table->integer('minimum_punch_time')->nullable();
            $table->integer('maximum_punch_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_policy');
    }
};
