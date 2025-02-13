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
        Schema::create('company_generic_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('object_type_id');
            $table->string('name', 250);
            $table->string('name_metaphone', 250)->nullable();
            $table->string('description', 250)->nullable();
            $table->integer('created_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->integer('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_generic_tag');
    }
};
