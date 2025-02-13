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
        Schema::create('policy_group_user', function (Blueprint $table) {
            $table->id();
            $table->integer('policy_group_id')->default(0); // Foreign key to `policy_group` table
            $table->integer('user_id')->default(0); // Foreign key to `users` table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_group_user');
    }
};
