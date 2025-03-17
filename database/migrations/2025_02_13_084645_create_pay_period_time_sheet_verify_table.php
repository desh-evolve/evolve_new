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
        Schema::create('pay_period_time_sheet_verify', function (Blueprint $table) {
            $table->id();
            $table->integer('pay_period_id');
            $table->integer('user_id');
            $table->integer('status_id');
            $table->tinyInteger('authorized')->default(0);
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->smallInteger('authorization_level')->default(99);
            $table->smallInteger('user_verified')->default(0);
            $table->integer('user_verified_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_period_time_sheet_verify');
    }
};
