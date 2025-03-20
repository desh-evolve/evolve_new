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
        Schema::create('pay_period', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->integer('pay_period_schedule_id');
            $table->integer('status_id');
            $table->boolean('is_primary')->default(0);
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->timestamp('advance_end_date')->nullable();
            $table->timestamp('advance_transaction_date')->nullable();
            $table->boolean('tainted')->default(0);
            $table->integer('tainted_by')->nullable();
            $table->integer('tainted_date')->nullable();
            $table->integer('is_hr_process')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_period');
    }
};
