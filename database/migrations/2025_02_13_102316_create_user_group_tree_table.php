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
        Schema::create('user_group_tree', function (Blueprint $table) {
            $table->integer('tree_id')->default(0); // Tree ID
            $table->integer('parent_id')->default(0); // Parent ID
            $table->integer('object_id')->default(0); // Object ID
            $table->bigInteger('left_id')->default(0); // Left ID
            $table->bigInteger('right_id')->default(0); // Right ID
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_group_tree');
    }
};
