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
        Schema::create('user_deduction', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('user_id'); // User ID (required)
            $table->integer('company_deduction_id'); // Company deduction ID (required)
            $table->string('user_value1', 250)->nullable(); // User value 1 (nullable)
            $table->string('user_value2', 250)->nullable(); // User value 2 (nullable)
            $table->string('user_value3', 250)->nullable(); // User value 3 (nullable)
            $table->string('user_value4', 250)->nullable(); // User value 4 (nullable)
            $table->string('user_value5', 250)->nullable(); // User value 5 (nullable)
            $table->string('user_value6', 250)->nullable(); // User value 6 (nullable)
            $table->string('user_value7', 250)->nullable(); // User value 7 (nullable)
            $table->string('user_value8', 250)->nullable(); // User value 8 (nullable)
            $table->string('user_value9', 250)->nullable(); // User value 9 (nullable)
            $table->string('user_value10', 250)->nullable(); // User value 10 (nullable)
            $table->integer('created_date')->nullable(); // Created date (nullable)
            $table->integer('created_by')->nullable(); // Created by (nullable)
            $table->integer('updated_date')->nullable(); // Updated date (nullable)
            $table->integer('updated_by')->nullable(); // Updated by (nullable)
            $table->integer('deleted_date')->nullable(); // Deleted date (nullable)
            $table->integer('deleted_by')->nullable(); // Deleted by (nullable)
            $table->tinyInteger('deleted')->default(0); // Deleted flag (default 0)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_deduction');
    }
};
