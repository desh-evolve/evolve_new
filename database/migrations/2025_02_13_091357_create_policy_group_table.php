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
        Schema::create('policy_group', function (Blueprint $table) {
            $table->id(); 
            $table->integer('company_id');
            $table->string('name', 250);
            $table->integer('exception_policy_control_id')->nullable(); // NULLable column
            $table->integer('accrual_policy_id')->nullable(); // NULLable column
            $table->integer('created_date')->nullable(); // Optional timestamp column
            $table->integer('created_by')->nullable(); // Optional foreign key column
            $table->integer('updated_date')->nullable(); // Optional timestamp column
            $table->integer('updated_by')->nullable(); // Optional foreign key column
            $table->integer('deleted_date')->nullable(); // Optional timestamp column
            $table->integer('deleted_by')->nullable(); // Optional foreign key column
            $table->tinyInteger('deleted')->default(0); // Default 0 for 'not deleted'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_group');
    }
};
