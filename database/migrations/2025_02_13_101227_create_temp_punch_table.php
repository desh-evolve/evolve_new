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
        Schema::create('temp_punch', function (Blueprint $table) {
            $table->mediumIncrements('id'); // Auto-incrementing primary key
            $table->dateTime('tstamp')->nullable(); // Timestamp column
            $table->date('crrdate')->nullable(); // Date column
            $table->integer('emp_id')->nullable(); // Employee ID, can be null
            $table->integer('mchin_id')->nullable(); // Machine ID, can be null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_punch');
    }
};
