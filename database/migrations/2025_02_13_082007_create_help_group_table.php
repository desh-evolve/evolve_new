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
        Schema::create('help_group', function (Blueprint $table) {
            $table->id();
            $table->integer('help_group_control_id')->default(0);
            $table->integer('help_id')->default(0);
            $table->integer('order_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_group');
    }
};
