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
        Schema::create('station_user_group', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('station_id'); // Foreign key to the `station` table
            $table->integer('group_id'); // Foreign key to the `group` table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('station_user_group');
    }
};
