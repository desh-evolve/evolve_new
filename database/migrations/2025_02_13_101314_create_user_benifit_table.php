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
        Schema::create('user_benifit', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key (default `id` as BIGINT)
            $table->integer('user_id'); // User ID (required)
            $table->string('item_name', 250); // Item name (required)
            $table->string('item_remark', 250); // Item remark (required)
            $table->integer('created_date'); // Created date (required)
            $table->integer('created_by'); // Created by (required)
            $table->integer('updated_date'); // Updated date (required)
            $table->integer('updated_by'); // Updated by (required)
            $table->integer('deleted_date'); // Deleted date (required)
            $table->integer('deleted_by'); // Deleted by (required)
            $table->tinyInteger('deleted')->default(0); // Deleted status (0 by default)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_benifit');
    }
};
