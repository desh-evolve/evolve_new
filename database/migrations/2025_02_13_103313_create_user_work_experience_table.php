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
        Schema::create('user_work_experience', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->integer('user_id'); // User ID
            $table->string('company_name', 250); // Company name
            $table->integer('from_date'); // From date
            $table->integer('to_date'); // To date
            $table->string('department', 250); // Department name
            $table->string('designation', 250); // Designation
            $table->string('remaks', 250); // Remarks
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
        Schema::dropIfExists('user_work_experience');
    }
};
