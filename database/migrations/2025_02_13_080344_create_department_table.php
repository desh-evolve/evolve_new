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
        Schema::create('department', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(0);
            $table->unsignedBigInteger('status_id');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('created_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_date')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->tinyInteger('deleted')->default(0);
            $table->unsignedBigInteger('manual_id')->nullable();
            $table->string('name_metaphone')->nullable();
            $table->string('other_id1')->nullable();
            $table->string('other_id2')->nullable();
            $table->string('other_id3')->nullable();
            $table->string('other_id4')->nullable();
            $table->string('other_id5')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department');
    }
};
