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
        Schema::create('user_default_company_deduction', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('user_default_id'); // Foreign key referencing user_default table
            $table->integer('company_deduction_id'); // Foreign key referencing company_deduction table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_default_company_deduction');
    }
};
