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
        Schema::create('system_log', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('user_id')->nullable(); // Nullable foreign key to `user` table
            $table->integer('object_id')->nullable(); // Nullable foreign key for object reference
            $table->string('table_name', 250)->nullable(); // Name of the table affected by the action
            $table->integer('action_id')->nullable(); // Action type, such as create, update, delete
            $table->text('description')->nullable(); // Description of the action
            $table->integer('date')->default(0); // Timestamp of the action
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_log');
    }
};
