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
        Schema::create('recurring_holiday', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('company_id'); // Foreign key to `company`
            $table->integer('type_id'); // Foreign key to `type`
            $table->string('name', 250); // Name of the recurring holiday
            $table->smallInteger('special_day')->nullable(); // Nullable field for special day
            $table->integer('week_interval')->nullable(); // Nullable field for week interval
            $table->integer('day_of_week')->nullable(); // Nullable field for day of the week
            $table->integer('day_of_month')->nullable(); // Nullable field for day of the month
            $table->integer('month_int')->nullable(); // Nullable field for month interval
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->integer('pivot_day_direction_id')->nullable(); // Nullable foreign key for pivot day direction
            $table->integer('always_week_day_id')->default(0); // Default value for always week day ID
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_holiday');
    }
};
