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
        Schema::create('user_education', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('user_id'); // Foreign key referencing user table
            $table->string('qualification_name'); // Qualification name
            $table->string('institute'); // Institute name
            $table->string('year'); // Year of qualification
            $table->string('remarks'); // Remarks related to the qualification
            $table->integer('created_date'); // Date when the record was created
            $table->integer('created_by'); // User who created the record
            $table->integer('updated_date'); // Date when the record was last updated
            $table->integer('updated_by'); // User who last updated the record
            $table->integer('deleted_date'); // Date when the record was deleted
            $table->integer('deleted_by'); // User who deleted the record
            $table->tinyInteger('deleted'); // Soft delete flag
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_education');
    }
};
