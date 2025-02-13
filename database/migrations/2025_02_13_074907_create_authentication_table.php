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
        Schema::create('authentication', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 250);
            $table->unsignedBigInteger('user_id');
            $table->string('ip_address', 250)->nullable();
            $table->integer('created_date');
            $table->integer('updated_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authentication');
    }
};
