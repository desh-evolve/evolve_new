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
        Schema::create('user_group', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('company_id'); // Company ID
            $table->string('name', 250); // Group name
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
        Schema::dropIfExists('user_group');
    }
};
