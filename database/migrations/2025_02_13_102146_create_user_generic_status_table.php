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
        Schema::create('user_generic_status', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('user_id'); // User ID
            $table->integer('batch_id'); // Batch ID
            $table->integer('status_id'); // Status ID
            $table->string('label', 1024)->nullable(); // Label (varchar(1024))
            $table->string('description', 1024)->nullable(); // Description (varchar(1024))
            $table->string('link', 1024)->nullable(); // Link (varchar(1024))
            $table->integer('created_date')->nullable(); // Creation timestamp
            $table->integer('created_by')->nullable(); // User who created the record
            $table->integer('updated_date')->nullable(); // Last update timestamp
            $table->integer('updated_by')->nullable(); // User who last updated the record
            $table->integer('deleted_date')->nullable(); // Deletion timestamp
            $table->integer('deleted_by')->nullable(); // User who deleted the record
            $table->tinyInteger('deleted')->default(0); // Soft delete flag
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_generic_status');
    }
};
