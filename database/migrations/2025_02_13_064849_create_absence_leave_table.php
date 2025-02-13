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
        Schema::create('absence_leave', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('short_code', 20);
            $table->unsignedBigInteger('time_seconds');
            $table->unsignedSmallInteger('related_leave_id');
            $table->unsignedBigInteger('related_leave_unit');
            $table->unsignedSmallInteger('status');
            $table->unsignedSmallInteger('deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absence_leave');
    }
};
