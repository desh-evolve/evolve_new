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
        Schema::create('round_interval_policy', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('company_id'); // Foreign key to `company` table
            $table->string('name'); // Name of the policy
            $table->integer('punch_type_id'); // Foreign key to `punch_type` table
            $table->integer('round_type_id'); // Foreign key to `round_type` table
            $table->integer('round_interval'); // Round interval value
            $table->tinyInteger('strict')->default(0); // Strict flag (0 or 1)
            $table->integer('grace')->nullable(); // Grace period (nullable)
            $table->integer('minimum')->nullable(); // Minimum value (nullable)
            $table->integer('maximum')->nullable(); // Maximum value (nullable)
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('round_interval_policy');
    }
};
