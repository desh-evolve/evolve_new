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
        Schema::create('allowance_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payperiod_id');
            $table->integer('worked_days');
            $table->integer('late_days');
            $table->integer('nopay_days');
            $table->integer('fullday_leave_days');
            $table->integer('halfday_leave_days');
            $table->integer('created_date');
            $table->unsignedBigInteger('created_by');
            $table->integer('updated_date');
            $table->unsignedBigInteger('updated_by');
            $table->integer('deleted_date');
            $table->unsignedBigInteger('deleted_by');
            $table->tinyInteger('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allowance_data');
    }
};
