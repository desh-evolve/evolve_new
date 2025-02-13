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
        Schema::create('absence_policy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name', 250);
            $table->unsignedBigInteger('type_id');
            $table->boolean('over_time')->default(0);
            $table->unsignedBigInteger('accrual_policy_id')->nullable();
            $table->unsignedBigInteger('premium_policy_id')->nullable();
            $table->unsignedBigInteger('pay_stub_entry_account_id')->nullable();
            $table->integer('created_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->unsignedBigInteger('wage_group_id')->default(0);
            $table->decimal('rate', 9, 4)->nullable();
            $table->decimal('accrual_rate', 9, 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absence_policy');
    }
};
