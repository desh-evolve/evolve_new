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
        Schema::create('user_life_circle', function (Blueprint $table) {
            $table->id(); // Auto-incrementing unsigned bigint ID
            $table->integer('user_id'); // User ID
            $table->string('current_designation', 150); // Current designation
            $table->string('new_designation', 150); // New designation
            $table->integer('current_salary'); // Current salary
            $table->integer('new_salary'); // New salary
            $table->integer('effective_date'); // Effective date
            $table->integer('created_date'); // Created date
            $table->integer('created_by'); // Created by
            $table->integer('updated_date'); // Updated date
            $table->integer('updated_by'); // Updated by
            $table->integer('deleted_date'); // Deleted date
            $table->integer('deleted_by'); // Deleted by
            $table->tinyInteger('deleted'); // Deleted flag
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_life_circle');
    }
};
