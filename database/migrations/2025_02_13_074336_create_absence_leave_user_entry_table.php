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
        Schema::create('absence_leave_user_entry', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('absence_leave_user_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('created_date');
            $table->unsignedBigInteger('created_by');
            $table->integer('updated_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->integer('deleted_date')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absence_leave_user_entry');
    }
};
