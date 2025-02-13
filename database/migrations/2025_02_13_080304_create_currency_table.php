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
        Schema::create('currency', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('status_id');
            $table->string('name');
            $table->string('iso_code', 5);
            $table->decimal('conversion_rate', 18, 10)->nullable();
            $table->smallInteger('auto_update')->nullable();
            $table->decimal('actual_rate', 18, 10)->nullable();
            $table->unsignedBigInteger('actual_rate_updated_date')->nullable();
            $table->decimal('rate_modify_percent', 18, 10)->nullable();
            $table->smallInteger('is_base')->default(0);
            $table->smallInteger('is_default')->default(0);
            $table->unsignedBigInteger('created_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_date')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->smallInteger('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency');
    }
};
