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
        Schema::create('system_log_detail', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('system_log_id'); // Foreign key to system_log table
            $table->string('field', 75)->nullable(); // Name of the field being changed
            $table->text('new_value')->nullable(); // The new value of the field
            $table->text('old_value')->nullable(); // The old value of the field
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_log_detail');
    }
};
