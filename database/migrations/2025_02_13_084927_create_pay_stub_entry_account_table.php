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
        Schema::create('pay_stub_entry_account', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->integer('status_id');
            $table->integer('type_id');
            $table->integer('ps_order');
            $table->string('name', 250);
            $table->integer('accrual_pay_stub_entry_account_id')->nullable();
            $table->string('debit_account', 250)->nullable();
            $table->string('credit_account', 250)->nullable();
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
        Schema::dropIfExists('pay_stub_entry_account');
    }
};
