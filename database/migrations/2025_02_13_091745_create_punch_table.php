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
        Schema::create('punch', function (Blueprint $table) {
            $table->id(); 
            $table->integer('punch_control_id'); // Foreign key to `punch_control`
            $table->integer('station_id')->nullable(); // Nullable foreign key to `station`
            $table->integer('type_id'); // Foreign key to `type`
            $table->integer('status_id'); // Foreign key to `status`
            $table->timestamp('time_stamp')->nullable(); // Timestamp for the punch time
            $table->timestamp('original_time_stamp')->nullable(); // Original timestamp for the punch
            $table->timestamp('actual_time_stamp')->nullable(); // Actual timestamp for the punch
            $table->integer('created_date')->nullable(); // Created date (integer, assuming Unix timestamp)
            $table->integer('created_by')->nullable(); // Created by user ID
            $table->integer('updated_date')->nullable(); // Updated date
            $table->integer('updated_by')->nullable(); // Updated by user ID
            $table->integer('deleted_date')->nullable(); // Deleted date
            $table->integer('deleted_by')->nullable(); // Deleted by user ID
            $table->boolean('deleted')->default(0); // Deleted flag
            $table->boolean('transfer')->default(0); // Transfer flag
            $table->decimal('longitude', 15, 10)->nullable(); // Longitude for punch
            $table->decimal('latitude', 15, 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('punch');
    }
};
