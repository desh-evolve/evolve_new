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
        Schema::create('wage_group', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->integer('company_id'); // Company ID
            $table->string('name', 250)->nullable(); // Name of the wage group
            $table->integer('created_date')->nullable(); // Created date
            $table->integer('created_by')->nullable(); // Created by
            $table->integer('updated_date')->nullable(); // Updated date
            $table->integer('updated_by')->nullable(); // Updated by
            $table->integer('deleted_date')->nullable(); // Deleted date
            $table->integer('deleted_by')->nullable(); // Deleted by
            $table->smallInteger('deleted')->default(0); // Deleted flag
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wage_group');
    }
};
