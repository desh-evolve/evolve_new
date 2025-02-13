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
        Schema::create('company_generic_tag_map', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('object_type_id');
            $table->unsignedBigInteger('object_id')->nullable();
            $table->unsignedBigInteger('tag_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_generic_tag_map');
    }
};
