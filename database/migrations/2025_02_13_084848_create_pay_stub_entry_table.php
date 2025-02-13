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
        Schema::create('pay_stub_entry', function (Blueprint $table) {
            $table->id();
            $table->integer('pay_stub_id');
            $table->decimal('rate', 20, 4)->nullable();
            $table->decimal('units', 20, 4)->nullable();
            $table->decimal('ytd_units', 20, 4)->nullable();
            $table->decimal('amount', 20, 4)->nullable();
            $table->decimal('ytd_amount', 20, 4)->nullable();
            $table->string('description', 250)->nullable();
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->tinyInteger('deleted')->default(0);
            $table->integer('pay_stub_entry_name_id');
            $table->integer('pay_stub_amendment_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_stub_entry');
    }
};
