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
        Schema::create('message_control', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('object_type_id');
            $table->integer('object_id');
            $table->smallInteger('require_ack')->default(0);
            $table->smallInteger('priority_id')->default(0);
            $table->string('subject', 250)->nullable();
            $table->string('body', 250)->nullable();
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
        Schema::dropIfExists('message_control');
    }
};
