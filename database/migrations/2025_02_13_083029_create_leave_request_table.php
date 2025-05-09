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
        Schema::create('leave_request', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->integer('user_id');
            $table->integer('designation_id');
            $table->integer('accurals_policy_id')->nullable();
            $table->double('amount')->nullable();
            $table->date('leave_from')->nullable();
            $table->date('leave_to')->nullable();
            $table->string('reason', 200)->nullable();
            $table->string('address_telephone', 200)->nullable();
            $table->integer('covered_by')->nullable();
            $table->integer('supervisor_id')->nullable();
            $table->integer('method')->nullable();
            $table->tinyInteger('is_covered_approved')->nullable();
            $table->tinyInteger('is_supervisor_approved')->nullable();
            $table->tinyInteger('is_hr_approved')->nullable();
            $table->integer('status')->nullable();
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->string('leave_time', 20)->nullable();
            $table->string('leave_end_time', 20)->nullable();
            $table->string('leave_dates', 2000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_request');
    }
};
