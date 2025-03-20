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
        Schema::create('user_generic_data', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('user_id')->nullable(); // Foreign key referencing user table (nullable)
            $table->string('script'); // Script name (varchar(250))
            $table->string('name'); // Name of the data (varchar(250))
            $table->tinyInteger('is_default')->default(0); // Flag indicating if the data is default
            $table->text('data')->nullable(); // Generic data (text)
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->integer('company_id'); // Foreign key referencing the company
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_generic_data');
    }
};
