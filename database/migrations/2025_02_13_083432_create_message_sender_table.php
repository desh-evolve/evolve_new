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
        Schema::create('message_sender', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('parent_id');
            $table->integer('message_control_id');
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->smallInteger('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_sender');
    }
};
