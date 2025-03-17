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
        Schema::create('pay_stub_amendment', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('pay_stub_entry_name_id');
            $table->integer('status_id')->default(10);
            $table->integer('effective_date')->nullable();
            $table->decimal('rate', 20, 4)->nullable();
            $table->decimal('units', 20, 4)->nullable();
            $table->decimal('amount', 20, 4)->nullable();
            $table->string('description', 250)->nullable();
            $table->tinyInteger('authorized')->default(0);
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->integer('recurring_ps_amendment_id')->nullable();
            $table->tinyInteger('ytd_adjustment')->default(0);
            $table->integer('type_id');
            $table->decimal('percent_amount', 20, 4)->nullable();
            $table->integer('percent_amount_entry_name_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_stub_amendment');
    }
};
