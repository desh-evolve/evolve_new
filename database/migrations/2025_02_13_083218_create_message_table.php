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
        Schema::create('message', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->integer('object_type_id');
            $table->integer('object_id');
            $table->integer('priority_id');
            $table->integer('status_id');
            $table->integer('status_date')->nullable();
            $table->string('subject', 250)->nullable();
            $table->text('body')->nullable();
            $table->tinyInteger('require_ack')->default(0);
            $table->tinyInteger('ack')->nullable();
            $table->integer('ack_date')->nullable();
            $table->integer('ack_by')->nullable();
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->tinyInteger('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message');
    }
};
