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
        Schema::create('user_identification', function (Blueprint $table) {
            $table->id(); // Auto-incrementing unsigned bigint ID
            $table->integer('user_id'); // User ID
            $table->integer('type_id'); // Type ID
            $table->integer('number'); // Number
            $table->text('value')->nullable(); // Value
            $table->text('extra_value')->nullable(); // Extra value
            $table->integer('created_date')->nullable(); // Created date
            $table->integer('created_by')->nullable(); // Created by
            $table->integer('updated_date')->nullable(); // Updated date
            $table->integer('updated_by')->nullable(); // Updated by
            $table->integer('deleted_date')->nullable(); // Deleted date
            $table->integer('deleted_by')->nullable(); // Deleted by
            $table->boolean('deleted')->default(0); // Deleted flag
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_identification');
    }
};
