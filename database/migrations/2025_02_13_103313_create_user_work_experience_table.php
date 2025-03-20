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
        Schema::dropIfExists('user_work_experience');
    }
};
