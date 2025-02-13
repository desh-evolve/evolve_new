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
        Schema::create('premium_policy_branch', function (Blueprint $table) {
            $table->id(); // auto-increment primary key (unsigned bigint)
            $table->integer('premium_policy_id'); // Foreign key to `premium_policy` table
            $table->integer('branch_id'); // Foreign key to `branch` table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium_policy_branch');
    }
};
