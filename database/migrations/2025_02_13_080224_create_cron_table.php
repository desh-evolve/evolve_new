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
        Schema::create('cron', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('status_id')->default(10);
            $table->string('name');
            $table->string('minute');
            $table->string('hour');
            $table->string('day_of_month');
            $table->string('month');
            $table->string('day_of_week');
            $table->string('command');
            $table->timestamp('last_run_date')->nullable();
            $table->unsignedBigInteger('created_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_date')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->tinyInteger('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron');
    }
};
