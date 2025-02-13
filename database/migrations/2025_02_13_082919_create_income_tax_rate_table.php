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
        Schema::create('income_tax_rate', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('country', 250)->nullable();
            $table->string('province', 250)->nullable();
            $table->integer('effective_date');
            $table->decimal('income', 10, 4);
            $table->decimal('rate', 10, 4);
            $table->decimal('constant', 10, 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_tax_rate');
    }
};
