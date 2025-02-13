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
        Schema::create('user_tax', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->integer('user_id'); // User ID
            $table->decimal('federal_claim', 9, 2); // Federal claim
            $table->decimal('provincial_claim', 9, 2); // Provincial claim
            $table->decimal('federal_additional_deduction', 9, 2); // Federal additional deduction
            $table->decimal('wcb_rate', 9, 2); // WCB rate
            $table->boolean('ei_exempt')->default(0); // EI exemption flag
            $table->integer('created_date')->nullable(); // Created date
            $table->integer('created_by')->nullable(); // Created by
            $table->integer('updated_date')->nullable(); // Updated date
            $table->integer('updated_by')->nullable(); // Updated by
            $table->integer('deleted_date')->nullable(); // Deleted date
            $table->integer('deleted_by')->nullable(); // Deleted by
            $table->boolean('deleted')->default(0); // Deleted flag
            $table->boolean('cpp_exempt')->default(0); // CPP exemption flag
            $table->boolean('federal_tax_exempt')->default(0); // Federal tax exemption flag
            $table->boolean('provincial_tax_exempt')->default(0); // Provincial tax exemption flag
            $table->decimal('vacation_rate', 9, 2); // Vacation rate
            $table->boolean('release_vacation')->default(0); // Release vacation flag
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tax');
    }
};
