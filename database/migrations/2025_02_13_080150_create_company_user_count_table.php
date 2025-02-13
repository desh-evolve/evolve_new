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
        Schema::create('company_user_count', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->date('date_stamp');
            $table->integer('active_users');
            $table->integer('inactive_users');
            $table->integer('deleted_users');
            $table->unsignedBigInteger('created_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_user_count');
    }
};
