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
        Schema::create('request', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('user_date_id'); // Foreign key to `user_date`
            $table->integer('type_id'); // Foreign key to `type`
            $table->integer('status_id'); // Foreign key to `status`
            $table->tinyInteger('authorized')->default(0);
            $table->integer('created_date')->nullable(); // Created date
            $table->integer('created_by')->nullable(); // Created by user ID
            $table->integer('updated_date')->nullable(); // Updated date
            $table->integer('updated_by')->nullable(); // Updated by user ID
            $table->integer('deleted_date')->nullable(); // Deleted date
            $table->integer('deleted_by')->nullable(); // Deleted by user ID
            $table->tinyInteger('deleted')->default(0); // Deleted flag (0 = not deleted)
            $table->smallInteger('authorization_level')->default(99); // Default value 99
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request');
    }
};
