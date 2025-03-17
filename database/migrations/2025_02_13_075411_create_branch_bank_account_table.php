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
        Schema::create('branch_bank_account', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('default_branch_id');
            $table->unsignedBigInteger('company_id');
            $table->string('institution', 15);
            $table->string('transit', 15);
            $table->string('account', 50);
            $table->string('bank_name', 100);
            $table->string('bank_branch', 100);
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_bank_account');
    }
};
