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
        Schema::create('recurring_ps_amendment_user', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('recurring_ps_amendment_id'); // Foreign key to `recurring_ps_amendment`
            $table->integer('user_id'); // Foreign key to `user`
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_ps_amendment_user');
    }
};
