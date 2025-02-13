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
        Schema::create('break_policy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name', 250);
            $table->unsignedBigInteger('type_id');
            $table->integer('trigger_time')->nullable();
            $table->integer('amount');
            $table->unsignedBigInteger('auto_detect_type_id');
            $table->integer('start_window')->nullable();
            $table->integer('window_length')->nullable();
            $table->integer('minimum_punch_time')->nullable();
            $table->integer('maximum_punch_time')->nullable();
            $table->smallInteger('include_break_punch_time')->nullable();
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->smallInteger('deleted')->default(0);
            $table->smallInteger('include_multiple_breaks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('break_policy');
    }
};
