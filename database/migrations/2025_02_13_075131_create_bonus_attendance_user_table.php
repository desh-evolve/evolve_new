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
            $table->integer('created_date');
            $table->integer('created_by');
            $table->integer('updated_date');
            $table->integer('updated_by');
            $table->integer('deleted_date');
            $table->integer('deleted_by');
            $table->integer('deleted');
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
