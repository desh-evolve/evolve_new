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
        Schema::create('pay_stub_entry_account_link', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->integer('total_gross')->nullable();
            $table->integer('total_employee_deduction')->nullable();
            $table->integer('total_employer_deduction')->nullable();
            $table->integer('total_net_pay')->nullable();
            $table->integer('regular_time')->nullable();
            $table->integer('monthly_advance')->nullable();
            $table->integer('monthly_advance_deduction')->nullable();
            $table->integer('employee_cpp')->nullable();
            $table->integer('employee_ei')->nullable();
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->tinyInteger('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_stub_entry_account_link');
    }
};
