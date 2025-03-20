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
        Schema::create('roe', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('user_id'); // Foreign key to `user` table
            $table->integer('pay_period_type_id'); // Foreign key to `pay_period_type` table
            $table->string('code_id'); // Code field
            $table->integer('first_date')->nullable(); // First date (nullable)
            $table->integer('last_date')->nullable(); // Last date (nullable)
            $table->integer('pay_period_end_date')->nullable(); // Pay period end date (nullable)
            $table->integer('recall_date')->nullable(); // Recall date (nullable)
            $table->decimal('insurable_hours', 9, 2); // Insurable hours
            $table->decimal('insurable_earnings', 9, 2); // Insurable earnings
            $table->decimal('vacation_pay', 9, 2)->nullable(); // Vacation pay (nullable)
            $table->string('serial')->nullable(); // Serial number (nullable)
            $table->string('comments')->nullable(); // Comments field (nullable)
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
        Schema::dropIfExists('roe');
    }
};
