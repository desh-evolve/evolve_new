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
        Schema::create('holiday_policy_recurring_holiday', function (Blueprint $table) {
            $table->id();
            $table->integer('holiday_policy_id')->default(0);
            $table->integer('recurring_holiday_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_policy_recurring_holiday');
    }
};
