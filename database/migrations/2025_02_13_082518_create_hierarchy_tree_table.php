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
        Schema::create('hierarchy_tree', function (Blueprint $table) {
            $table->integer('tree_id')->default(0);
            $table->integer('parent_id')->default(0);
            $table->integer('object_id')->default(0);
            $table->bigInteger('left_id')->default(0);
            $table->bigInteger('right_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hierarchy_tree');
    }
};
