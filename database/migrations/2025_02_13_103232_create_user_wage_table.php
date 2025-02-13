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
        Schema::create('user_wage', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->integer('user_id'); // User ID
            $table->integer('type_id'); // Wage type ID
            $table->decimal('wage', 20, 4)->nullable(); // Wage amount
            $table->date('effective_date')->nullable(); // Effective date
            $table->integer('created_date')->nullable(); // Created date
            $table->integer('created_by')->nullable(); // Created by
            $table->integer('updated_date')->nullable(); // Updated date
            $table->integer('updated_by')->nullable(); // Updated by
            $table->integer('deleted_date')->nullable(); // Deleted date
            $table->integer('deleted_by')->nullable(); // Deleted by
            $table->boolean('deleted')->default(0); // Deleted flag
            $table->integer('weekly_time')->nullable(); // Weekly time
            $table->decimal('labor_burden_percent', 9, 2)->nullable(); // Labor burden percent
            $table->text('note')->nullable(); // Note
            $table->integer('wage_group_id')->default(0); // Wage group ID
            $table->decimal('hourly_rate', 20, 4)->nullable(); // Hourly rate
            $table->decimal('budgetary_allowance', 20, 4); // Budgetary allowance
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_wage');
    }
};
