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
            $table->integer('accurals_policy_id');
            $table->double('amount');
            $table->date('leave_from');
            $table->date('leave_to');
            $table->string('reason', 200);
            $table->string('address_telephone', 200);
            $table->integer('covered_by');
            $table->integer('supervisor_id');
            $table->integer('method');
            $table->tinyInteger('is_covered_approved');
            $table->tinyInteger('is_supervisor_approved');
            $table->tinyInteger('is_hr_approved');
            $table->integer('status');
            $table->integer('created_date');
            $table->integer('created_by');
            $table->integer('updated_date');
            $table->integer('updated_by');
            $table->integer('deleted_date');
            $table->integer('deleted_by');
            $table->tinyInteger('deleted');
            $table->string('leave_time', 20);
            $table->string('leave_end_time', 20);
            $table->string('leave_dates', 2000);
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
