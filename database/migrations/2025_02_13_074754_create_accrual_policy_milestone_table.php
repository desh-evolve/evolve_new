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
        Schema::create('accrual_policy_milestone', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accrual_policy_id');
            $table->decimal('length_of_service', 9, 2)->nullable();
            $table->smallInteger('length_of_service_unit_id')->nullable();
            $table->decimal('length_of_service_days', 9, 2)->nullable();
            $table->decimal('accrual_rate', 18, 4)->nullable();
            $table->integer('minimum_time')->nullable();
            $table->integer('maximum_time')->nullable();
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->integer('rollover_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accrual_policy_milestone');
    }
};
