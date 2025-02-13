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
        Schema::create('user_census', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('user_id'); // User ID (required)
            $table->string('dependant', 150); // Dependant name (required)
            $table->string('name', 250); // Name of the dependant (required)
            $table->string('relationship', 50); // Relationship with the user (required)
            $table->integer('dob'); // Date of birth (required)
            $table->string('nic', 30); // NIC (required)
            $table->string('gender', 20); // Gender (required)
            $table->integer('created_date'); // Created date (required)
            $table->integer('created_by'); // Created by (required)
            $table->integer('updated_date'); // Updated date (required)
            $table->integer('updated_by'); // Updated by (required)
            $table->integer('deleted_date'); // Deleted date (required)
            $table->integer('deleted_by'); // Deleted by (required)
            $table->tinyInteger('deleted')->default(0); // Deleted status (0 by default)

            // Primary Key
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_census');
    }
};
