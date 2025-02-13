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
        Schema::create('accrual', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('accrual_policy_id');
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('user_date_total_id')->nullable();
            $table->timestamp('time_stamp')->nullable();
            $table->decimal('amount', 18, 4)->nullable();
            $table->integer('created_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->unsignedBigInteger('leave_requset_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accrual');
    }
};
