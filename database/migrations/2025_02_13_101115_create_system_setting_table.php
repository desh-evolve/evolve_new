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
        Schema::create('system_setting', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('name', 250); // Name of the setting
            $table->text('value')->nullable(); // The value of the setting, can be NULL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_setting');
    }
};
