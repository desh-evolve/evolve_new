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
        Schema::create('other_field', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->integer('type_id');
            $table->string('other_id1', 250)->nullable();
            $table->string('other_id2', 250)->nullable();
            $table->string('other_id3', 250)->nullable();
            $table->string('other_id4', 250)->nullable();
            $table->string('other_id5', 250)->nullable();
            $table->string('other_id6', 250)->nullable();
            $table->string('other_id7', 250)->nullable();
            $table->string('other_id8', 250)->nullable();
            $table->string('other_id9', 250)->nullable();
            $table->string('other_id10', 250)->nullable();
            $table->boolean('required_other_id1')->default(0);
            $table->boolean('required_other_id2')->default(0);
            $table->boolean('required_other_id3')->default(0);
            $table->boolean('required_other_id4')->default(0);
            $table->boolean('required_other_id5')->default(0);
            $table->boolean('required_other_id6')->default(0);
            $table->boolean('required_other_id7')->default(0);
            $table->boolean('required_other_id8')->default(0);
            $table->boolean('required_other_id9')->default(0);
            $table->boolean('required_other_id10')->default(0);
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->boolean('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_field');
    }
};
