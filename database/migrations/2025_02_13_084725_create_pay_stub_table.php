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
        Schema::create('pay_stub', function (Blueprint $table) {
            $table->id();
            $table->integer('pay_period_id')->default(0);
            $table->integer('user_id')->default(0);
            $table->integer('status_id')->default(0);
            $table->integer('status_date')->nullable();
            $table->integer('status_by')->nullable();
            $table->tinyInteger('advance')->default(0);
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
            $table->tinyInteger('tainted')->default(0);
            $table->tinyInteger('temp')->nullable()->default(0);
            $table->integer('currency_id')->nullable();
            $table->decimal('currency_rate', 18, 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_stub');
    }
};
