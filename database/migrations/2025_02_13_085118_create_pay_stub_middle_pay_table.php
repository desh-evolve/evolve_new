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
        Schema::create('pay_stub_middle_pay', function (Blueprint $table) {
            $table->id();
            $table->integer('pay_period_id');
            $table->integer('user_id');
            $table->decimal('amount', 10, 4);
            $table->tinyInteger('deleted');
            $table->integer('created_date');
            $table->integer('created_by');
            $table->integer('updated_date');
            $table->integer('updated_by');
            $table->integer('deleted_date');
            $table->integer('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_stub_middle_pay');
    }
};
