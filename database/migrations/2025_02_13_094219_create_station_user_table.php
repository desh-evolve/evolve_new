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
        Schema::create('station_user', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('station_id')->default(0); // Foreign key to the `station` table with default value of 0
            $table->integer('user_id')->default(0); // Foreign key to the `user` table with default value of 0
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('station_user');
    }
};
