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
        Schema::create('bonus_attendance_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bonus_attendance_id');
            $table->unsignedBigInteger('user_id');
            $table->float('nopay');
            $table->float('leave_balance');
            $table->float('amount');
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus_attendance_user');
    }
};
