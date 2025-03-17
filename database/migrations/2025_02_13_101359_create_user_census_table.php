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
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);

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
