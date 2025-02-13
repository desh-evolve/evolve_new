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
        Schema::create('accrual_balance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('accrual_policy_id');
            $table->decimal('balance', 18, 4)->nullable();
            $table->integer('banked_ytd')->default(0);
            $table->integer('used_ytd')->default(0);
            $table->integer('awarded_ytd')->default(0);
            $table->integer('created_date')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accrual_balance');
    }
};
