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
        Schema::create('recurring_schedule_template_control', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('company_id'); // Foreign key to `company`
            $table->string('name', 250); // Name of the template control
            $table->string('description', 250)->nullable(); // Description (nullable)
            $table->integer('created_date')->nullable(); // Created date
            $table->integer('created_by')->nullable(); // Created by
            $table->integer('updated_date')->nullable(); // Updated date
            $table->integer('updated_by')->nullable(); // Updated by
            $table->integer('deleted_date')->nullable(); // Deleted date
            $table->integer('deleted_by')->nullable(); // Deleted by
            $table->tinyInteger('deleted')->default(0); // Deleted flag, defaults to 0
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_schedule_template_control');
    }
};
