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
        Schema::create('over_time_policy', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->string('name', 250);
            $table->integer('type_id');
            $table->integer('trigger_time');
            $table->integer('max_time');
            $table->decimal('rate', 9, 4)->nullable();
            $table->integer('accrual_policy_id')->nullable();
            $table->decimal('accrual_rate', 9, 4)->nullable();
            $table->integer('pay_stub_entry_account_id')->nullable();
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->boolean('deleted')->default(0);
            $table->integer('wage_group_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('over_time_policy');
    }
};
