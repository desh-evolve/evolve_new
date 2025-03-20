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
        Schema::create('recurring_ps_amendment', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('company_id'); // Foreign key to `company`
            $table->integer('status_id')->default(10); // Default value of 10 for status
            $table->integer('start_date'); // Start date (timestamp or date)
            $table->integer('end_date')->nullable(); // Nullable end date
            $table->integer('frequency_id'); // Frequency of the amendment
            $table->string('name', 250)->nullable(); // Amendment name
            $table->string('description', 250)->nullable(); // Amendment description
            $table->integer('pay_stub_entry_name_id'); // Foreign key for pay stub entry name
            $table->decimal('rate', 20, 4)->nullable(); // Rate for the amendment
            $table->decimal('units', 20, 4)->nullable(); // Units for the amendment
            $table->decimal('amount', 20, 4)->nullable(); // Amount for the amendment
            $table->decimal('percent_amount', 20, 4)->nullable(); // Percentage amount for the amendment
            $table->integer('percent_amount_entry_name_id')->nullable(); // Foreign key for percent amount entry name
            $table->string('ps_amendment_description', 250)->nullable(); // Additional description for the amendment
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->integer('type_id'); // Foreign key for amendment type
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_ps_amendment');
    }
};
